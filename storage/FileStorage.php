<?php

namespace ferguson\upload\storage;

use Yii;

class FileStorage extends Storage implements StorageInterface
{
    public $dir;

    public $url;

    public $resize = [
        'width' => '90',
        'height' => '120',
    ];

    public $watermark = [
        'switch' => false,
        'type' => 'font',
        'position' => 'right',
        'font' => [
            'ttf' => '',
            'text' => '',
            'size' => '10',
        ],
    ];

    public function init()
    {
        // check system support images library.
        $this->imagick = extension_loaded('imagick');
        $this->gd = extension_loaded('gd');
        // init upload.
        $this->setFilePath(rtrim(Yii::getAlias($this->dir), '\//') . date('/Y/md/'));
        $this->setFileUrl(rtrim(Yii::getAlias($this->url), '\//') . date('/Y/md/'));
        parent::init();
        $this->createDirectory($this->getFilePath());
    }

    public function save()
    {
        $this->upload();

        if (!$this->imagick) {
            $this->image = new \Imagick($this->tmp_filename);
            $this->saveImagick();
        } elseif ($this->gd) {
            $this->image = imagecreatefromstring(@file_get_contents($this->tmp_filename));
            $this->saveGD();
        }

        if (!$this->image) {
            rename($this->tmp_filename, $this->getFileFullPath());
        }
    }

    public function resize(array $config = [])
    {
        if ($this->imagick) {
            $this->image = new\Imagick($this->tmp_filename);
        } elseif ($this->gd) {
            $this->image = imagecreatefromstring(@file_get_contents($this->tmp_filename));
        }
    }

    public function delete($file)
    {
        return @unlink(rtrim(Yii::getAlias($this->dir), '\//') . $file);
    }

    private function saveImagick()
    {
        $this->image->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $this->image->setImageCompressionQuality(60);
        $this->image->writeImage($this->getFileFullPath());
        //todo create thumb img.
        /*$this->imageSize = $this->image->getImagePage();

        if ($this->calculateSize()) {
            list($x, $y, $width, $height) = $this->calculateSize();
            $thumb = new \Imagick();
            $this->image->thumbnailImage($this->resize['width'], $this->resize['height'], true);
            $draw = new \ImagickDraw();
            $draw->composite($this->image->getImageCompose(), $x, $y, $width, $height, $this->image);

            $thumb->newImage($width, $height, 'rgba(0,0,0,0)', $this->image->getFormat());
            $thumb->drawImage($draw);
            $thumb->setImagePage($width, $height, 0, 0);
            $thumb->writeImage($this->getFileFUllThumbPath());
        }*/

        $this->image->destroy();
        @unlink($this->tmp_filename);
    }

    private function saveGD()
    {
        //header('Content-Type: ' . $this->fileMime);
        //$out = "image{$this->fileExtension}";
        switch ($this->getFileExtension()){
            case 'png':
                imagepng($this->image, $this->getFileFullPath());
                break;
            case 'gif':
                imagegif($this->image, $this->getFileFullPath());
                break;
            case 'wbmp':
                imagewbmp($this->image, $this->getFileFullPath());
                break;
            default:
                imagejpeg($this->image, $this->getFileFullPath());
        }
        //todo. create thumb img.

        imagedestroy($this->image);
        @unlink($this->tmp_filename);
    }

    private function calculateSize()
    {
        $width = $this->resize['width'];
        $height = $this->resize['height'];
        //
        $src_width = $this->imageSize['width'];
        $src_height = $this->imageSize['height'];
        if ($width == 0 && $height == 0) {
            return;
        }

        // 上下填充白色。
        if ($src_width / $src_height > $width / $height) {
            return [0, intval(($height - $src_height * $width / $src_width) / 2), $width, $height];
        } else {
            return [intval(($width - $src_width * $height / $src_height) / 2), 0, $width, $height];
        }

    }

    private function watermark()
    {
        if ($this->watermark['switch'] === true) {
            //todo.
        }
    }

    public function getFileFullThumbName()
    {
        return $this->getFileUrl() . $this->getFileName() . "_{$this->resize['width']}_{$this->resize['height']}." . $this->getFileExtension();
    }

    public function getFileFUllThumbPath()
    {
        return $this->getFilePath() . $this->getFileName() . "_{$this->resize['width']}_{$this->resize['height']}." . $this->getFileExtension();
    }

}