<?php

namespace ferguson\upload;

/**
 * Class Module
 * @package ferguson\upload
 *
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'ferguson\upload';

    public $id = 'upload';

    public $storage = 'file';

    public function init()
    {
        if(!isset($this->params['dir']) || empty($this->params['dir'])){
            throw new \Exception('Invalid config setting, dir can not be blank.');
        }
        if(!isset($this->params['url']) || empty($this->params['url'])){
            throw new \Exception('Invalid config setting, url can not be blank.');
        }
        parent::init();
    }
}