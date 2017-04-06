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

    public $config = [];

    public function init()
    {
        if(!isset($this->config['dir']) || empty($this->config['dir'])){
            throw new \Exception('Invalid config setting, dir can not be blank.');
        }
        if(!isset($this->config['url']) || empty($this->config['url'])){
            throw new \Exception('Invalid config setting, url can not be blank.');
        }
        \Yii::$app->params['ferguson.upload.storage'] = $this->storage;
        \Yii::$app->params['ferguson.upload.config'] = $this->config;
        parent::init();
    }
}