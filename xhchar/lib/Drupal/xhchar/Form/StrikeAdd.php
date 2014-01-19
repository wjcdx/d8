<?php

namespace Drupal\xhchar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\xhchar\StrikeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StrikeAdd extends FormBase {

  /**
   * @var \Drupal\xhchar\StrikeManager
   */
  protected $strikeManager;

  /**
   * Constructs a new StrikeAdd object.
   *
   * @param \Drupal\xhchar\StrikeManager $manager
   */
  public function __construct(StrikeManager $manager) {
    $this->strikeManager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('xhchar.strike.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'xhchar_strike_add_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param string $default_ip
   *   (optional) IP address to be passed on to drupal_get_form() for use as the
   *   default value of the IP address form field.
   */
  public function buildForm(array $form, array &$form_state) {
    $form['no'] = array(
      '#title' => $this->t('Sequence Number'),
      '#type' => 'textfield',
	  '#size' => 48,
      '#maxlength' => 40,
	  '#required' => TRUE,
      '#description' => $this->t('Enter a sequence number.'),
    );
    $form['name'] = array(
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#size' => 48,
      '#maxlength' => 40,
	  '#required' => TRUE,
      '#description' => $this->t('Enter a descriptive name.'),
    );
    $form['desc'] = array(
      '#title' => $this->t('Description'),
      '#type' => 'textfield',
      '#size' => 48,
      '#maxlength' => 40,
	  '#required' => TRUE,
  	);
	$form['#attributes']['enctype'] = 'multipart/form-data';
    $form['image'] = array(
      '#title' => $this->t('Image'),
      '#type' => 'file',
      '#description' => $this->t('Upload the image of the strike.'),
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
	$strike = array();
	$strike["no"] = trim($form_state['values']['no']);
	$strike["name"] = trim($form_state['values']['name']);
	$strike["desc"] = trim($form_state['values']['desc']);

	$file = file_save_upload('image', $form_state, array(), FALSE, 0);
	if (!isset($file)) {
		$this->setFormError('image', t("Failed to save the file."));
	}
	$strike["fid"] = $file->id();

	$this->strikeManager->add($strike);
	drupal_set_message($this->t('The strike %name has been added.', array('%name' => $strike["name"])));
	$form_state['redirect_route']['route_name'] = 'xhchar.strike.add';
  }

}
