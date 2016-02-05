<?php
use Cake\Routing\Router;

Router::plugin(
    'S3FileManager',
    ['path' => '/s3-file-manager'],
    function ($routes) {
        $routes->extensions(['json']);
        $routes->fallbacks('DashedRoute');
    }
);