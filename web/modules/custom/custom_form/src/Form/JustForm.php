<?php

namespace Drupal\custom_form\Form;

use Drupal\node\Entity\Node;
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

  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form_cache = $this->cache->get('form:custom_form');

    $form_cache = $form_cache->data['time'] ?? 'None';

    $entity_type_manager = \Drupal::service('entity_type.manager');
    $query = $entity_type_manager->getStorage('node')->getQuery();
    $query->condition('langcode', 'en');
    $query->accessCheck(FALSE);
    $query->range(0, 50);
    $query->sort('nid');
    $node_ids = $query->execute();

    if ($node_ids) {
      $form['entity_container'] = [
        '#type' => 'container',
      ];
      foreach ($node_ids as $id) {
        $node = Node::load($id);
        $form['entity_container'][$id] = [
          '#type' => 'item',
          '#title' => "ID: $id, Title: {$node?->getTitle()}, Bundle: {$node?->bundle()}",
        ];

      }
    }
    $form['cache_container'] = [
      '#type' => 'container',
    ];

    $form['cache_container']['cache'] = [
      '#type' => 'markup',
      '#markup' => "Form cache: $form_cache</br>",
    ];

    $form['cache_container']['set'] = [
      '#type' => 'submit',
      '#name' => 'set',
      '#value' => $this->t('Set'),
    ];

    $form['cache_container']['delete'] = [
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
