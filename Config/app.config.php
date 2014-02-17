<?php
define('DEBUG', true);

define('TEMPLATE_NAME', 'default');

define("WEIXIN_TOKEN", "xxxxxxxxxx");

$databases['default']['default'] = array(
  'driver'   => 'pgsql',
  'database' => 'xxxxxx',
  'username' => 'xxxxxx',
  'password' => 'xxxxxx',
  'host'     => '192.168.2.1',
  'port'	   => '5432'
);

$databases['default2']['default'][] = array(
  'driver'    => 'mysql',
  'database'  => 'xxxxxx',
  'username'  => 'xxxxxx',
  'password'  => 'xxxxxx',
  'host'     => '192.168.2.1',
  'port'      => '3306'
);


/*
  $databases['default']['slave'][] = array(
  'driver' => 'mysql',
  'database' => 'drupaldb2',
  'username' => 'username',
  'password' => 'secret',
  'host' => 'dbserver2',
);

$databases['default']['slave'][] = array(
  'driver' => 'mysql',
  'database' => 'drupaldb3',
  'username' => 'username',
  'password' => 'secret',
  'host' => 'dbserver3',
);

$databases['extra']['default'] = array(
  'driver' => 'sqlite',
  'database' => 'files/extradb.sqlite',
);
 */
$soap['uri'] = 'http://webservice.example.com';
$soap['login'] = 'soap';
$soap['password'] = 'xxxxxxxxx';
$soap['compression'] = 'SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP';

?>