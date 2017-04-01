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

    public $config = [
        'dir' => '@webroot',
        'url' => '@web',
    ];

    public function init()
    {
        \Yii::$app->params['ferguson.upload.storage'] = $this->storage;
        \Yii::$app->params['ferguson.upload.config'] = $this->config;
        parent::init();
    }
}