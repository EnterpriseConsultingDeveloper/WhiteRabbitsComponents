<?php

namespace S3FileManager\Utils;

use Cake\Http\Client;

class WRClient extends \Cake\Http\Client
{

  protected $_defaultConfig = [
    'adapter' => 'Cake\Http\Client\Adapter\Stream',
    'host' => null,
    'port' => null,
    'scheme' => 'http',
    'timeout' => 30,
    'ssl_verify_peer' => false,
    'ssl_verify_peer_name' => false,
    'ssl_verify_depth' => false,
    'ssl_verify_host' => false,
    'redirect' => false,
  ];


}