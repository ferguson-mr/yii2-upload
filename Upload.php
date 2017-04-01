<?php

namespace ferguson\upload;

use ferguson\base\components\ArrayHelper;

use ferguson\upload\helper\MimeHelper;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class Upload extends \yii\widgets\InputWidget
{
    const TYPE_IMAGE = MimeHelper::TYPE_IMAGE;
    const TYPE_ARCHIVE = MimeHelper::TYPE_ARCHIVE;
    const TYPE_OFFICE = MimeHelper::TYPE_OFFICE;
    const TYPE_OTHER = MimeHelper::TYPE_OTHER;

    public $clientOptions = [];

    /**
     * This will used for input or hidden input container upload file path.
     * @var string
     */
    private $id;

    public $directory;

    public function init()
    {
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        $this->id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        parent::init();
    }

    public function run()
    {
        $this->initSetting();
        echo $this->renderInput();
    }

    protected function initSetting()
    {
        $this->options = ArrayHelper::merge([
            'preview' => false,
            'class' => 'upload-frame upload-theme-success',
        ], $this->options);

        $this->clientOptions = ArrayHelper::merge([
            'url' => Url::to('/upload/default/index'),
            'delete' => Url::to('/upload/default/delete'),
            'type' => MimeHelper::TYPE_IMAGE,
            'max_size' => '2mb',
        ], $this->clientOptions);

        $this->registerAssets();
    }

    protected function renderInput()
    {
        if ($this->hasModel()) {
            $this->name = Html::getInputName($this->model, $this->attribute);
        }
        $input = Html::hiddenInput($this->name, $this->value);

        $content = Html::tag('div', $input, $this->options);
        return $content;
    }

    protected function registerAssets()
    {
        $view = $this->getView();
        $upat = UploadAsset::register($view);
        $this->clientOptions['path'] = $upat->baseUrl;
        $id = $this->getId();
        $filters = ArrayHelper::getValue($this->clientOptions, 'type');
        if (is_string($filters)) {
            $filters = explode(',', $filters);
        }
        foreach ($filters as $key => $filter) {
            $this->clientOptions['filter'][] = $this->getMimeType($filter, true);
        }

        $options = Json::htmlEncode($this->clientOptions);
        $form = Json::htmlEncode([
            'name' => $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name,
            'value' => $this->model->{$this->attribute}
        ]);
        $js = <<<JS
        $('#{$id}').uploaded($options, $form);
JS;
        $view->registerJs($js);
    }

    /**
     * @param null $type
     * @param boolean $front
     * @return mixed|null
     */
    private function getMimeType($type = null, $front = false)
    {
        $map = MimeHelper::extensionTypeMap();

        if ($front === true) {
            return isset($map[$type]) ? [
                'title' => ucfirst($type) . ' Files',
                'extensions' => implode(',', $map[$type]),
            ] : [];
        }

        if ($type !== null) {
            return isset($map[$type]) ? $map[$type] : null;
        }
        return $map;
    }
}