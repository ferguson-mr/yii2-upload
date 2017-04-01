<?php

namespace ferguson\upload;

use Yii;
use ferguson\base\AssetBundle;

class UploadAsset extends AssetBundle
{
    public $js = self::EMPTY_ASSET;

    public $css = [
        'css/upload.css',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('js', ['js/plupload.full.min.js', 'js/i18n/' . Yii::$app->language . '.js', 'js/jquery.plupload.js', 'css/iconfont.js']);
        //$this->setupAssets('css', ['css/upload']);
        parent::init();
    }
}