<?php

namespace Drupal\sina\Form;

use Drupal\Core\Form\ConfigFormBase;

class Admin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sina_admin_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param string $default_ip
   *   (optional) IP address to be passed on to drupal_get_form() for use as the
   *   default value of the IP address form field.
   */
	public function buildForm(array $form, array &$form_state) {
		$config = $this->configFactory->get('sina.settings');
    $form['weibo_app_key'] = array(
        '#title' => t('App Key'),
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => $config->get('weibo_app_key'),
    );

    $form['weibo_app_secret'] = array(
        '#title' => t('App Secret'),
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => $config->get('weibo_app_secret'),
    );

    $form['weibo_button_url'] = array(
        '#title' => t('Sign in button image URL'),
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => $config->get('weibo_button_url', 'http://www.sinaimg.cn/blog/developer/wiki/240.png'),
    );

		return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
	public function validateForm(array &$form, array &$form_state) {
		parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
		$key = trim($form_state['values']['weibo_app_key']);
		$secret = trim($form_state['values']['weibo_app_secret']);
		$url = trim($form_state['values']['weibo_button_url']);

		$this->configFactory->get('sina.settings')
			->set('weibo_app_key', $key)
			->set('weibo_app_secret', $secret)
			->set('weibo_button_url', $url)
			->save();

		parent::submitForm($form, $form_state);
  }

}
