<?php

namespace Drupal\custom_plugin\Plugin\Block;

use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "custom_plugin",
 *   admin_label = @Translation("Custom Plugin Example"),
 *   category = @Translation("Custom Plugin")
 * )
 */
class CustomPlugin extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      'tab' => [
        '#type' => 'link',
        '#title' => t('Go to the home page'),
        '#url' => Url::fromRoute('<front>'),
      ],
    ];

    return $build;
  }

}
