<?php
return array (
  'backend' => 
  array (
    'frontName' => 'admin',
  ),
  'queue' => 
  array (
    'amqp' => 
    array (
      'host' => '',
      'port' => '',
      'user' => '',
      'password' => '',
      'virtualhost' => '/',
      'ssl' => '',
    ),
  ),
  'db' => 
  array (
    'connection' => 
    array (
      'indexer' => 
      array (
        'host' => 'mysql',
        'dbname' => 'eemagento',
        'username' => 'root',
        'password' => 'thaohien',
        'model' => 'mysql4',
        'engine' => 'innodb',
        'initStatements' => 'SET NAMES utf8;',
        'active' => '1',
        'persistent' => NULL,
      ),
      'default' => 
      array (
        'host' => 'mysql',
        'dbname' => 'eemagento',
        'username' => 'root',
        'password' => 'thaohien',
        'model' => 'mysql4',
        'engine' => 'innodb',
        'initStatements' => 'SET NAMES utf8;',
        'active' => '1',
      ),
    ),
    'table_prefix' => '',
  ),
  'crypt' => 
  array (
    'key' => '8ad70cd441d9372a543f578eea151cfb',
  ),
  'session' => 
  array (
    'save' => 'files',
  ),
  'resource' => 
  array (
    'default_setup' => 
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'developer',
  'cache_types' => 
  array (
    'config' => 0,
    'layout' => 0,
    'block_html' => 0,
    'collections' => 0,
    'reflection' => 0,
    'db_ddl' => 0,
    'eav' => 0,
    'full_page' => 0,
    'config_integration' => 0,
    'config_integration_api' => 0,
    'target_rule' => 0,
    'translate' => 0,
    'config_webservice' => 0,
    'compiled_config' => 1,
  ),
  'install' => 
  array (
    'date' => 'Mon, 10 Apr 2017 07:06:06 +0000',
  ),
);
