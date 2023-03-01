<?php

namespace Drupal\admin_toolbar_version\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AdminToolbarVersionSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_toolbar_version';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'admin_toolbar_version.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('admin_toolbar_version.settings');

    $form['#title'] = $this->t('Admin Toolbar Version');
    $form['#tree'] = true;

    foreach ($config->get('environments') as $id => $environment) {
      $form['environments'][$id] = [
        '#type' => 'details',
        '#title' => $environment['name'],
        '#open' => false,
        'name' => [
          '#type' => 'textfield',
          '#title' => 'Name',
          '#description' => 'The name that should be displayed in the toolbar',
          '#default_value' => $environment['name']
        ],
        'domain' => [
          '#type' => 'textfield',
          '#title' => 'Domain',
          '#description' => 'Enter a preg_match pattern to match the host (eg. "/www\.domain\.com/" ).',
          '#default_value' => $environment['domain']
        ],
        'variable' => [
          '#type' => 'textfield',
          '#title' => 'Variable',
          '#description' => 'Enter the value as available in $_ENV',
          '#default_value' => $environment['variable'],
        ],
        'color' => [
          '#type' => 'textfield',
          '#title' => 'Color',
          '#description' => 'Enter the css color for the background of the toolbar item (eg. #FF0000 or red)',
          '#default_value' => $environment['color']
        ],
        'git' => [
          '#type' => 'textfield',
          '#title' => 'Git',
          '#description' => 'Path to the GIT HEAD file (relative to Drupal root), Leave empty to not show GIT info.',
          '#default_value' => $environment['git']
        ]
      ];
    }

    $form['environments'][0] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => 'Add new environment',
      'name' => [
        '#type' => 'textfield',
        '#title' => 'Name',
        '#description' => 'The name that should be displayed in the toolbar. Leave empty to remove an environment.',
        '#default_value' => ''
      ],
      'domain' => [
        '#type' => 'textfield',
        '#title' => 'Domain',
        '#description' => 'Enter a preg_match pattern to match the host (eg. "/www\.domain\.com/" ).',
        '#default_value' => ''
      ],
      'variable' => [
        '#type' => 'textfield',
        '#title' => 'Variable',
        '#description' => 'Enter the value as available in $_ENV',
        '#default_value' => ''
      ],
      'color' => [
        '#type' => 'textfield',
        '#title' => 'Color',
        '#description' => 'Enter the css color for the background of the toolbar item (eg. #FF0000 or red)',
        '#default_value' => ''
      ],
      'git' => [
        '#type' => 'textfield',
        '#title' => 'Git',
        '#description' => 'Path to the GIT HEAD file (relative to Drupal root), Leave empty to not show GIT info.',
        '#default_value' => '/.git/HEAD'
      ]
    ];

    /** @var \Drupal\Core\Extension\ExtensionList $extension_list */
    $extension_list = \Drupal::service('extension.list.module');
    $list = $extension_list->getList();
    $list_options = [];
    foreach ($list as $name => $item) {
      $list_options[$name] = $item->getName();
    }

    $form['version_source'] = [
      '#type' => 'select',
      '#options' => $list_options,
      '#title' => 'Version source',
      '#description' => 'The module to grab the version information from.',
      '#default_value' => $config->get('version_source') ?? \Drupal::installProfile()
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get values.
    $environments = $form_state->getValue('environments');

    // Make sure the new environment gets a uuid.
    if (isset($environments[0])) {
      $uuid_service = \Drupal::service('uuid');
      $uuid = $uuid_service->generate();
      $environments[$uuid] = $environments[0];
      unset($environments[0]);
    }

    // Remove empty environments.
    $environments = array_filter($environments, function($environment) {
      return !empty($environment['name']);
    });

    // Save environments.
    $config = $this->config('admin_toolbar_version.settings');
    $config->set('environments', $environments);

    // Save version source.
    $config->set('version_source',  $form_state->getValue('version_source', ''));

    $config->save();

    // Clear cache so admin menu can rebuild.
    \Drupal::service('plugin.manager.menu.link')->rebuild();

    parent::submitForm($form, $form_state);
  }

}
