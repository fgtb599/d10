<?php

namespace Drupal\custom_form\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class JustForm extends FormBase {

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private CacheBackendInterface $cache;

  public function getFormId() {
    return 'custom_form';
  }

  /**
   * @param \Drupal\Core\Cache\CacheBackendInterface $staticCache
   *   Cache backend.
   */
  public function __construct(CacheBackendInterface $staticCache) {
    $this->cache = $staticCache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('cache.default'),
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form_cache = $this->cache->get('form:custom_form');

    $form_cache = $form_cache->data['time'] ?? 'None';

    $form['cache'] = [
      '#type' => 'markup',
      '#markup' => "Form cache: $form_cache</br>",
    ];

    $form['set'] = [
      '#type' => 'submit',
      '#name' => 'set',
      '#value' => $this->t('Set'),
    ];

    $form['delete'] = [
      '#type' => 'submit',
      '#name' => 'delete',
      '#value' => $this->t('Delete'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {

    if ($form_state->getTriggeringElement()['#name'] == 'set') {
      $time = new \DateTime();
      $time = $time->format('Y-m-d H:i:s');
      $data = [
        'time' => $time,
      ];
      $this->cache->set('form:custom_form', $data, CacheBackendInterface::CACHE_PERMANENT, ['node:1']);
    }

    if ($form_state->getTriggeringElement()['#name'] == 'delete') {
      $this->cache->delete('form:custom_form');
    }

  }
}
