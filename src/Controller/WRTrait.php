<?php
/**
 * Class WRTrait
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */

namespace S3FileManager\Controller;
use S3FileManager\Event\LoggedInCustomerListener;

trait WRTrait
{
    /**
     * {@inheritDoc}
     */
    public function loadModel($modelClass = null, $type = 'Table') {
        $model = parent::loadModel($modelClass, $type);
        $listener = new LoggedInCustomerListener($this->request);
        $model->setEventManager()->attach($listener);
        return $model;
    }
}