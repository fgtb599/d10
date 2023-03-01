<?php
namespace Drupal\admin_toolbar_version;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

class VersionInfoManager {

  use StringTranslationTrait;

  /**
   * VersionInfoManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Extension\ModuleExtensionList $extensionList
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleExtensionList $extensionList, FileSystemInterface $fileSystem) {
    $this->extension_list = $extensionList;
    $this->config_factory = $config_factory;
    $this->config = $this->config_factory->getEditable('admin_toolbar_version.settings');
    $this->file_system = $fileSystem;
  }

  /**
   * Get item url.
   *
   * @return string
   */
  public function getUrl() {
    $url = $this->config->get('version_url') ?? "/";
    if (empty($url)) {
      $url = "/";
    }
    $url = Url::fromUserInput($url)->toUriString();
    return $url;
  }

  /**
   * Get application version from source module or install profile.
   *
   * @return string
   */
  public function getApplicationVersion() {
    $version = '';

    $version_source = $this->config->get('version_source');
    if (empty($version_source)){
      $version_source = \Drupal::installProfile();
    }

    $info = $this->extension_list->getExtensionInfo($version_source);

    if (isset($info['version'])) {
      $version = $info['version'];
    }

    return $version;
  }

  /**
   * Get the current drupal version.
   *
   * @return string
   */
  public function getDrupalVersion() {
    return \Drupal::VERSION;
  }

  /**
   * Get the current GIT branch.
   *
   * @return string
   */
  public function getGitBranch() {
    $branch = '';
    $environment = $this->getEnvironmentConfig();
    // Extract GIT information.
    if ($environment && $environment['git']) {
      $git = $environment['git'];
      $path = $this->file_system->realpath(DRUPAL_ROOT . $git);
      if (file_exists($path)) {
        $git_file = file_get_contents($path);
        $branch = trim(implode('/', array_slice(explode('/', $git_file ?: ''), 2)));
      }
    }
    return $branch;
  }

  /**
   * Get the environment name.
   *
   * @return string
   */
  public function getEnvironment() {
    $environment = NULL;
    $config = $this->getEnvironmentConfig();
    // Extract GIT information.
    if ($config) {
      $environment = $config['name'];
    }
    return $environment;
  }

  /**
   * Assemble a menu title.
   *
   * @return string
   */
  public function getTitle() {

    $title = [
      'drupal' => $this->getDrupalVersion(),
      'version' => $this->getApplicationVersion(),
      'environment' => $this->getEnvironment(),
      'git' => $this->getGitBranch()
    ];

    return implode(' - ', array_filter($title));
  }

  /**
   * Get custom styling.
   */
  public function getStyle() {
    $style = [];
    $environment = $this->getEnvironment();
    if ($environment) {
      $config = $this->getEnvironmentConfig();
      $style = [
        'color' => $config['color'] ?? '#0000FF',
        'icon' => preg_replace('@[^a-z0-9_]+@', '_', trim(strtolower($environment)))
      ];
    }

    return $style;
  }

  protected function getEnvironmentConfig() {
    static $environment = false;

    if (!$environment) {

      // Get environment.
      $request = \Drupal::request();
      $environments = $this->config->get('environments');
      foreach ($environments as $econfig) {

        // Skip if domain isn't matched.
        if (!empty($econfig['domain']) && !preg_match($econfig['domain'], $request->getHost())) {
          continue;
        }

        // Skip if $_ENV isn't matched.
        if (!empty($econfig['variable']) && !isset($_ENV[$econfig['variable']])) {
          continue;
        }

        // Skip if neither domain or $_ENV variable is given.
        if (empty($econfig['domain']) && empty($econfig['variable'])) {
          continue;
        }

        $environment = $econfig;

        break;
      }
    }

    return $environment;
  }

}
