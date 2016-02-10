<?php

namespace S3FileManager\Controller;

use App\Controller\AppController as BaseController;

class AppController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->helpers[] = 'S3FileManager.S3File';
    }
}
