<?php

// Docker
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'host' => 'mariadb',
  'username' => 'drupal',
  'password' => 'drupal',
  'database' => 'drupal',
  'prefix' => '',
);

$base_url = 'http://drupal.docker.localhost:8000';

// Enable display all errors.
$conf['error_level'] = 2;
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// Local temporary directory.
$conf['file_temporary_path'] = '/tmp';
// Local private directory.
$conf['file_private_path'] = '../private/files';

$conf['drupal_http_request_fails'] = FALSE;

// Reroute email
$conf['reroute_email_address'] = 'roberto.peruzzo@studioaqua.it';

$conf['theme_debug'] = TRUE;