<?php

/**
 * @file
 * Definition of Drupal\rest\RequestHandler.
 */

namespace Drupal\xhchar;

use Drupal\file\Entity\File;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Template\Attribute;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Acts as intermediate request forwarder for resource plugins.
 */
class ImageManager extends ContainerAware {

  /**
   * Handles a web API request.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   * @param mixed $id
   *   The resource ID.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object.
   */
	public function upload(Request $request) {

		//$user = \Drupal::currentUser();
		$response = new Response();

	  $file_upload = $request->files->get("upload_image");

	  if (!isset($file_upload)) {
			$response->setContent("-1");
			return $response;
	  }

		$uploaded_files = $file_upload;
		if (!is_array($file_upload)) {
			$uploaded_files = array($file_upload);
		}

		$files = array();
	  foreach ($uploaded_files as $i => $file_info) {
			switch ($file_info->getError()) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$files[$i] = FALSE;
				continue;
				
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
					$files[$i] = FALSE;
					continue;
				
				case UPLOAD_ERR_OK:
				// Final check that this is a valid upload, if it isn't, use the
				// default error handler.
					if (is_uploaded_file($file_info->getRealPath())) {
						break;
					}

				// Unknown error
				default:
					$files[$i] = FALSE;
					continue;
			}

			// Begin building file entity.
			$values = array(
				'uid' => 0;//$user->id(),
				'status' => 0,
				'filename' => $file_info->getClientOriginalName(),
				'uri' => $file_info->getRealPath(),
				'filesize' => $file_info->getSize(),
			);
			$values['filemime'] = file_get_mimetype($values['filename']);
			$file = entity_create('file', $values);

			// init of all variables used in file_save_upload@file.module
			$extensions = '';
			$validators = array();
			$destination = FALSE;
			
			// No validator was provided, so add one using the default list.
			// Build a default non-munged safe list for file_munge_filename().
			$extensions = 'jpg jpeg gif png';
			$validators['file_validate_extensions'] = array();
			$validators['file_validate_extensions'][0] = $extensions;

			if (!empty($extensions)) {
				// Munge the filename to protect against possible malicious extension
				// hiding within an unknown file type (ie: filename.html.foo).
				$file->setFilename(file_munge_filename($file->getFilename(), $extensions));
			}

			// If the destination is not provided, use the temporary directory.
			if (empty($destination)) {
				$destination = 'temporary://';
			}

			// Assert that the destination contains a valid stream.
			$destination_scheme = file_uri_scheme($destination);
			if (!file_stream_wrapper_valid_scheme($destination_scheme)) {
				//drupal_set_message(t('The file could not be uploaded because the destination %destination is invalid.', array('%destination' => $destination)), 'error');
				$files[$i] = FALSE;
				continue;
			}

			$file->source = $form_field_name;
			// A file URI may already have a trailing slash or look like "public://".
			if (substr($destination, -1) != '/') {
				$destination .= '/';
			}
			$file->destination = file_destination($destination . $file->getFilename(), $replace);
			// If file_destination() returns FALSE then $replace === FILE_EXISTS_ERROR and
			// there's an existing file so we need to bail.
			if ($file->destination === FALSE) {
				//drupal_set_message(t('The file %source could not be uploaded because a file by that name already exists in the destination %directory.', array('%source' => $form_field_name, '%directory' => $destination)), 'error');
				$files[$i] = FALSE;
				continue;
			}

			// Add in our check of the the file name length.
			$validators['file_validate_name_length'] = array();

			// Call the validation functions specified by this function's caller.
			$errors = file_validate($file, $validators);

			// Check for errors.
			if (!empty($errors)) {
				$files[$i] = FALSE;
				continue;
			}

			// Move uploaded files from PHP's upload_tmp_dir to Drupal's temporary
			// directory. This overcomes open_basedir restrictions for future file
			// operations.
			$file->uri = $file->destination;
			if (!drupal_move_uploaded_file($file_info->getRealPath(), $file->getFileUri())) {
				//form_set_error($form_field_name, $form_state, t('File upload error. Could not move uploaded file.'));
				watchdog('file', 'Upload error. Could not move uploaded file %file to destination %destination.', array('%file' => $file->filename, '%destination' => $file->uri));
				$files[$i] = FALSE;
				continue;
			}

			// Set the permissions on the new file.
			drupal_chmod($file->getFileUri());

			// If we are replacing an existing file re-use its database record.
			if ($replace == FILE_EXISTS_REPLACE) {
				$existing_files = entity_load_multiple_by_properties('file', array('uri' => $file->getFileUri()));
				if (count($existing_files)) {
					$existing = reset($existing_files);
					$file->fid = $existing->id();
				}
			}

			// If we made it this far it's safe to record this file in the database.
			$file->save();
			$files[$i] = $file;
		}

		$ids = array();
		foreach ($files as $file) {
			if ($file) {
				$ids[] = $file->id();
			}
		}
		$response = new Response();
		$response->setContent(implode(',', $ids));
		return $response;
  }

}
