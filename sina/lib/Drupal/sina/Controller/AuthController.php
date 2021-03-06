<?php

/**
 * @file
 * Contains \Drupal\sina\Controller\SinaController.
 */

namespace Drupal\sina\Controller;

use Drupal\sina\sae\OAuthException;
use Drupal\sina\sae\SaeTOAuthV2;
use Drupal\sina\WeiboManager;
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
   * WeiboManager object.
   *
   * @var \Drupal\sina\WeiboManager
   */
  protected $weiboManager;

  /**
   * Constructs a ConfigFactory object.
   *
   * @param
   *
   */
	public function __construct() {
		$config = $this->config('sina.settings');
		
		//$key = $config->get('weibo_app_key', '');
    //$secret = $config->get('weibo_app_secret', '');
		//$this->saeTOAuthV2 = new SaeTOAuthV2($key, $secret);

		$this->weiboManager = \Drupal::service('sina.manager');
  }

  /**
   * {@inheritdoc}
   */
	public function response() {
		global $base_url;
    //$aurl = $this->saeTOAuthV2->getAuthorizeURL($base_url . '/sina/callback');
		//return new RedirectResponse($aurl);
		return $this->redirect('sina.callback');
	}
	
	/**
	 * Weibo OAuth callback:
	 * 0. if no weibo_uid exist in sina table, create it.
	 * 1. if there's no user for the weibo_uid, create it;
	 * 2. login as the user;
	 */
	public function callback() {

		$_REQUEST['code'] = 'request_code';
		if (isset($_REQUEST['code'])) {

			global $base_url;

			$keys = array(
				'code' => $_REQUEST['code'],
				'redirect_uri' => $base_url . '/weibo/callback',
			);

			try {
				//$token = $this->saeTOAuthV2->getAccessToken('code', $keys);
				//$_SESSION['weibo_token'] = $token;
			} catch (OAuthException $e) {
			}

			//$access_token = $_SESSION['weibo_token']['access_token'];
			$access_token = 'abcdef-access-token';
			if (isset($access_token)) {
				$user = drupal_anonymous_user();
				// Find an existing user.
				//$wid = $_SESSION['weibo_token']['uid']);
				$wid = 'weibo_uid';
				$sync = 0;
				$wba = $this->weiboManager->findByWid($wid);
				
				// user not existed
				// 1. create a user;
				// 2. create a weibo user in sina table;
				if (empty($wba)) {
					$values = array(
						'name' => $wid,
					);
					$user = entity_create('user', $values);
					$user->setPassword($access_token);
					$user->setEmail('wjcdx@qq.com');
					$user->activate();
					$user->save();

					$uid = $user->id();
					$account = array(
						'uid' => $uid,
						'weibo_uid' => $wid,
						'access_token' => $access_token,
						'sync' => 0,
					);
					$this->weiboManager->add($account);

				} else {
					$sync = $wba->sync;
					$user = user_load($wba->uid);
				}

				if ($sync == 0) {
					drupal_set_message(t('You are logged in with a Sina Weibo Account %user. <a href="!user_reset">Reset your password.</a>',
						array('%user' => $user->getUsername(), '!user_reset' => url('user/password'))));
				}

				// 3. login as the user;
				user_login_finalize($user);
			}
		}
		return $this->redirect('<front>');
	}

}
