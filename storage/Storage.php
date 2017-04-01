<?php

namespace ferguson\upload\storage;

use Yii;
use ferguson\upload\helper\MimeHelper;

abstract class Storage
{
    /**
     * file path on server
     * @var string
     */
    private $filePath;

    /**
     * file host on server whether view's be used.
     * @var string
     */
    private $fileUrl;

    /**
     * file's name without extension.
     * @var string
     */
    private $fileName;

    /**
     * file's extension without dot.
     * @var string
     */
    private $fileExtension;

    /**
     * file's mime type.
     * @var string
     */
    private $fileMime;

    /**
     * tmp file's full name with extension and full path, it's a absolute path.
     * @var string
     */
    protected $tmp_filename;

    /**
     * tmp directory where files uploaded.
     * @var string
     */
    protected $tmp_directory;

    /**
     * @var \Imagick|Object
     */
    protected $image;

    protected $imageSize;

    protected $imagick;

    protected $gd;

    public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }

        $this->init();
    }

    public function init()
    {
        $this->imagick = extension_loaded('imagick');
        $this->gd = extension_loaded('gd');

        $this->tmp_directory = rtrim(Yii::getAlias('@webroot'), '\//') . '/tmp/';
        $this->createDirectory($this->tmp_directory);
        $this->fileName = date('YmdHis') . uniqid();
    }

    protected function upload()
    {
        $parts = Yii::$app->request->post('chunks', 1);
        $part = Yii::$app->request->post('chunk', 0);

        $this->tmp_filename = $this->tmp_directory . md5(Yii::$app->request->post('name', uniqid('file_')));
        if (!$tmp_file = @fopen($this->tmp_filename, $parts ? 'ab' : 'wb')) {
            throw new \Exception('Failed to open tmp stream.', 102);
        }
        if ($_FILES){
            $tmp = $_FILES['file'];
            switch ($tmp['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    throw new \Exception('Failed to uploaded, file size reach ini setting.', 103);
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    throw new \Exception('Failed to uploaded, file size reach ini form setting.', 103);
                    break;
                case UPLOAD_ERR_PARTIAL:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new \Exception('Failed to uploaded, can not found file.', 104);
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new \Exception('Failed to uploaded, can not found temp file.', 104);
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    throw new \Exception('Failed to uploaded, can not write into temp file.', 105);
                    break;
                case UPLOAD_ERR_EXTENSION:
                    throw new \Exception('Failed to uploaded, can not support file extension.', 106);
                    break;
                case UPLOAD_ERR_OK:
                default:
                    $stream = @file_get_contents($tmp['tmp_name']);
                    break;
            }
        } else {
            if (!$stream = Yii::$app->request->getRawBody()) {
                throw new \Exception('Failed to open input stream.', 101);
            }
        }
        fwrite($tmp_file, $stream);
        @fclose($tmp_file);

        if (!$parts || $part == $parts - 1) {
            $this->validFileSize();
            $this->validFileMime();
        } else {
            throw new \Exception('Failed to uploaded file, file not complete.', 100);
        }
    }

    private function validFileSize()
    {

    }

    /**
     * validation upload file's mime type
     * @throws \Exception
     */
    private function validFileMime()
    {
        $fi = new \finfo(FILEINFO_MIME_TYPE);
        $this->fileMime = $fi->file($this->tmp_filename);
        $type = Yii::$app->request->post('type');
        $types = MimeHelper::extensionTypeMap($type);
        $allowMimes = [];
        foreach ($types as $extension) {
            $allowMimes[$extension] = MimeHelper::mimeType($extension);
        }
        if (!in_array($this->fileMime, $allowMimes)) {
            throw new \Exception('Failed to uploaded, can not support file mime.', 106);
        }
        $this->fileExtension = array_search($this->fileMime, $allowMimes);
    }

    /**
     * create directory or chmod directory auth on server.
     * @param $path
     * @return null
     */
    protected function createDirectory($path)
    {
        if (!is_dir($path)){
            @mkdir($path, 0755, true);
        }
        if(!$d = opendir($path)){
            @chmod($path, 0755);
        }
        @closedir($d);
    }

    public function setFileMime($value){
        $this->fileMime = $value;
    }

    public function setFilePath($value){
        $this->filePath = $value;
    }

    /**
     * return upload file's path on server.
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFileUrl($value){
        $this->fileUrl = $value;
    }

    /**
     * return upload file's extension.
     * @return mixed
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * return upload file's url host.
     * @return mixed
     */
    public function getFileUrl()
    {
        return $this->fileUrl;
    }



    public function getFileFullPath(){
        return $this->getFilePath() . $this->getFileNameWithExtension();
    }

    /**
     * return upload file's name whether created by server without extension.
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * return upload file's name whether created by server with extension.
     * @return string
     */
    public function getFileNameWithExtension()
    {
        return $this->fileName . '.' . $this->fileExtension;
    }

    /**
     * return upload file's url whether can direct open in browser.
     * @return string
     */
    public function getFileFullName()
    {
        return $this->getFileUrl() . $this->getFileNameWithExtension();
    }

    public function getFileFullThumbName()
    {
        return '';
    }
}