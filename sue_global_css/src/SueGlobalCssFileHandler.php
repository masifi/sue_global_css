<?php

namespace Drupal\sue_global_css;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

class SueGlobalCssFileHandler {

  protected $fileSystem;
  protected $configFactory;
  protected $logger;
  protected $cacheTagsInvalidator;

  public function __construct(
    FileSystemInterface $file_system,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
    CacheTagsInvalidatorInterface $cache_tags_invalidator
  ) {
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('sue_global_css');
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  public function saveCssFile($css_content) {
    try {
      $css_directory = 'public://sue_global_css';
      if (!$this->fileSystem->prepareDirectory($css_directory, FileSystemInterface::CREATE_DIRECTORY)) {
        throw new \Exception("Unable to create or write to directory: $css_directory");
      }
      
      $css_file = $css_directory . '/global.css';
      if (!$this->fileSystem->saveData($css_content, $css_file, FileSystemInterface::EXISTS_REPLACE)) {
        throw new \Exception("Unable to save CSS file: $css_file");
      }

      // Update the timestamp to force cache invalidation
      $this->configFactory->getEditable('sue_global_css.settings')
        ->set('css_updated', time())
        ->save();

      // Invalidate library cache tags
      $this->cacheTagsInvalidator->invalidateTags(['library_info']);

      $this->logger->info('Global CSS file successfully updated.');
    } catch (\Exception $e) {
      $this->logger->error('Failed to save global CSS file: @message', ['@message' => $e->getMessage()]);
      throw $e;
    }
  }
}