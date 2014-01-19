<?php

/**
 * @file
 * Contains \Drupal\sina\Controller\SinaController.
 */

namespace Drupal\sina\Controller;

use Drupal\sina\OAuthException;
use Drupal\sina\SaeTOAuthV2;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller routines for sina routes.
 */
class AuthController extends ControllerBase {

  /**
   * SaeTOAuthV2 object.
   *
   * @var \Drupal\sina\SaeTOAuthV2
   */
  protected $saeTOAuthV2;

  /**
   * Constructs a ConfigFactory object.
   *
   * @param
   *
   */
	public function __construct() {
		$config = $this->config('sina.settings');
		
		$key = $config->get('weibo_app_key', '');
    $secret = $config->get('weibo_app_secret', '');
		$this->saeTOAuthV2 = new SaeTOAuthV2($key, $secret);
  }

  /**
   * {@inheritdoc}
   */
	public function response() {
		global $base_url;
    $aurl = $this->saeTOAuthV2->getAuthorizeURL($base_url . '/sina/callback');
		return new RedirectResponse($aurl);
	}
	
	public function callback() {
		global $base_url;
		$user = $this->currentUser();

    if (isset($_REQUEST['code'])) {
			$keys = array(
					'code' => $_REQUEST['code'],
					'redirect_uri' => $base_url . '/weibo/callback',
			);
			try {
					$token = $this->saeTOAuthV2->getAccessToken('code', $keys);
					$_SESSION['weibo_token'] = $token;
			} catch (OAuthException $e) {
			}

			if ($_SESSION['weibo_token']['access_token']) {
					// Find an existing user.
					$result = db_select('sina')
							->fields('sina')
							->condition('weibo_uid', $_SESSION['weibo_token']['uid'])
							->execute()
							->fetchObject();

					if (isset($result->uid) && $result->uid > 0) {
							$form_state['uid'] = $result->uid;
							user_login_submit(array(), $form_state);
							return $this->redirect('user.page');
					}
					else {
							return $this->redirect('sina.bind_new');
					}
					//return '<pre>'. check_plain(print_r($_SESSION['weibo_token'], 1)) .'</pre>';
			}
		}
		drupal_set_message($this->t('Sina Login failed.'), 'warning');
		return $this->redirect('<front>');
	}

}
