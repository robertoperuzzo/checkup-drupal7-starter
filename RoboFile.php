<?php

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Site\Settings;
use Robo\Exception\TaskException;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {

  use \Boedah\Robo\Task\Drush\loadTasks;

  const PROJECT_FOLDER = __DIR__;
  const DRUPAL_ROOT_FOLDER = self::PROJECT_FOLDER . '/web';
  const DATABASE_DUMP_FOLDER = self::PROJECT_FOLDER . '/backups';

  /**
   * Site.
   *
   * @var string
   */
  protected $site = 'default';

  /**
   * Setup from database.
   *
   * @command install:database
   *
   * @param string $dump_file
   *
   * @return \Robo\Collection\CollectionBuilder
   */
  public function installDatabase($dump_file) {
    $task_list = [
      'sqlDrop' => $this->initDrush()
        ->drush('sql:drop')
        ->option("--yes"),
      'sqlCli' => $this->initDrush()
        ->drush('sql:cli < ')
        ->arg($dump_file),
      'cacheRebuild' => $this->initDrush()->drush('cache:rebuild'),
    ];
    $this->getBuilder()->addTaskList($task_list);
    return $this->getBuilder();
  }

  /**
   * Compute various metrics.
   *
   * @command analyze:php
   */
  public function computeMetrics() {
    $this->taskExec('vendor/bin/phpqa')
      ->option('analyzedDirs', 'web/modules/custom')
      ->option('buildDir', 'reports')
      ->option('ignoredFiles', '*\\\.css,*\\\.md,*\\\.txt,*\\\.info,*\\\.yml')
      ->option(
        'tools',
        'phpcpd:0,phpcs:0,phpmd:0,phpmetrics,phploc,pdepend,security-checker,phpstan'
      )
      ->option('execution', 'no-parallel')
      ->option('report')
      ->run();
  }

  /**
   * Scaffold file for Drupal.
   *
   * Create the settings.php and service.yml from default file template or twig
   * twig template.
   *
   * @return $this
   *
   * @throws \Robo\Exception\TaskException
   */
  public function scaffold() {
    $base = self::DRUPAL_ROOT_FOLDER . "/sites/{$this->site}";
    $scaffold = self::PROJECT_FOLDER . "/scaffold";

    // Throws exception if dir site folder not exists.
    if (!file_exists($base)) {
      new TaskException($this, 'You have to copy Drupal codebase in /web folder.');
    }

    $source = $scaffold . DIRECTORY_SEPARATOR . 'tpl.settings.local.php';
    $destination = $base . DIRECTORY_SEPARATOR . 'settings.local.php';
    $this->getBuilder()->addTaskList([
      'add-settings-local' => $this->taskFilesystemStack()
        ->copy($source, $destination)
    ]);

    return $this->getBuilder();
  }

  /**
   * Sets up drush defaults.
   *
   * @param string $site
   * @return \Boedah\Robo\Task\Drush\DrushStack
   */
  protected function initDrush($site = 'default') {
    return $this->taskDrushStack(self::PROJECT_FOLDER . '/vendor/bin/drush')
      ->drupalRootDirectory(self::DRUPAL_ROOT_FOLDER)
      ->uri($site);
  }

}
