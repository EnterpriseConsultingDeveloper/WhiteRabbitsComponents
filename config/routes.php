<?php
use Cake\Routing\Router;

Router::plugin(
    'S3FileManager',
    ['path' => '/s3_file_manager'],
    function ($routes) {
        $routes->extensions(['json']);
        $routes->fallbacks('InflectedRoute');
        $routes->connect(
            '/files/media/**',
            array('controller' => 'files', 'action' => 'media')
        );
    }

);

