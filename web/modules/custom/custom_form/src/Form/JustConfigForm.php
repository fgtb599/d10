<?php

namespace Drupal\custom_form\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class JustConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'custom_form.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_form_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['value_1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value 1'),
      '#default_value' => $config->get('value_1'),
    ];

    $form['value_2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value 2'),
      '#default_value' => $config->get('value_2'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('value_1', $form_state->getValue('value_1'))
      // You can set multiple configurations at once by making
      // multiple calls to set().
      ->set('value_2', $form_state->getValue('value_2'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
