<?php

/**
 * @file
 * Contains \Drupal\sina\Controller\SinaController.
 */

namespace Drupal\sina\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for sina routes.
 */
class BindController extends ControllerBase {

	public function bindNew() {
		return $this->redirect('user.register');
	}
	
	public function bindExist() {
		return $this->redirect('user.login');
	}

}
