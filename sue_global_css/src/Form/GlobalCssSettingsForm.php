<?php

namespace Drupal\sue_global_css\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;

class GlobalCssSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['sue_global_css.settings'];
  }

  public function getFormId() {
    return 'sue_global_css_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sue_global_css.settings');

    $form['global_css'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Global CSS'),
      '#default_value' => $config->get('global_css'),
      '#description' => $this->t('Enter the CSS that should be applied globally.'),
      '#rows' => 20,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $css_content = $form_state->getValue('global_css');
    $this->config('sue_global_css.settings')
      ->set('global_css', $css_content)
      ->save();

    // Generate and save CSS file
    $css_directory = 'public://sue_global_css';
    \Drupal::service('file_system')->prepareDirectory($css_directory, FileSystemInterface::CREATE_DIRECTORY);
    
    $css_file = $css_directory . '/global.css';
    \Drupal::service('file_system')->saveData($css_content, $css_file, FileSystemInterface::EXISTS_REPLACE);

    // Update the timestamp to force cache invalidation
    $this->config('sue_global_css.settings')
      ->set('css_updated', time())
      ->save();

    parent::submitForm($form, $form_state);
  }
}