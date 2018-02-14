<?php
use Cake\Routing\Router;


Router::plugin(
    'S3FileManager',
    ['path' => '/s3_file_manager'],
    function ($routes) {
        $routes->extensions(['json']);
        $routes->fallbacks('InflectedRoute');
        $routes->connect(
            '/files/media_auth/**',
            array('controller' => 'Files', 'action' => 'media_auth')
        );
        $routes->connect(
            '/Files/media_auth/**',
            array('controller' => 'Files', 'action' => 'media_auth')
        );
        $routes->connect(
            '/files/media/**',
            array('controller' => 'Files', 'action' => 'media')
        );
        $routes->connect(
            '/Files/media/**',
            array('controller' => 'Files', 'action' => 'media')
        );
    }

);