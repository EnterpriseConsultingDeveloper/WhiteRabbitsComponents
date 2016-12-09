<?php

namespace S3FileManager\WRClient;

use Cake\Network\Http\Client;


class WRClient extends \Cake\Network\Http\Client
{

  protected $_defaultConfig = [
    'adapter' => 'Cake\Http\Client\Adapter\Stream',
    'host' => null,
    'port' => null,
    'scheme' => 'http',
    'timeout' => 30,
    'ssl_verify_peer' => SSL_VERIFY_PEER,
    'ssl_verify_peer_name' => SSL_VERIFY_PEER_NAME,
    'ssl_verify_depth' => SSL_VERIFY_DEPTH,
    'ssl_verify_host' => SSL_VERIFY_HOST,
    'redirect' => false,
  ];


}