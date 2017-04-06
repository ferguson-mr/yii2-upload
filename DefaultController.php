<?php

namespace ferguson\upload;

use Yii;
use ferguson\upload\storage\FileStorage;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DefaultController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'upload' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        try {
            /*$filename = ucfirst($this->module->storage) . 'Storage';
            if (file_exists(__DIR__ . '/storage/' . $filename . '.php')) {
                $className = __NAMESPACE__ . '\\storage\\' . $filename;

                $uploader = new $className($this->module->config);
            } else {
                $uploader = new FileStorage($this->module->config);
            }*/
            $uploader = new FileStorage(Yii::$app->params['ferguson.upload.config']);
            $uploader->save();

            return $this->asJson([
                'status' => true,
                'url' => $uploader->getFileFullUrl(),
                'ext' => $uploader->getFileExtension(),
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'status' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        }


    }

    public function actionResize()
    {

    }

    public function actionDelete()
    {
        $file = Yii::$app->request->post('file');
        $uploader = new FileStorage($this->module->config);

        $result = true;
        if($uploader->exist($file)){
            $result = $uploader->delete($file);
        }
        if(Yii::$app->request->isAjax){
            return $this->asJson([
                'status' => $result
            ]);
        }else{
            return [
                'status' => $result,
            ];
        }
    }
}