# Yii2-upload


This is a uploaded library with [plupload](https://github.com/moxiecode/plupload) used to upload file.
> NOTE: This extension depends on the [yiisoft/yii2](https://github.com/yiisoft/yii2) extension. Check the [composer.json](http://git.mlfh.info/ferguson/yii2-upload/src/master/composer.json) for this extension's requirements and dependencies.  PHP environment require `fileinfo`, if upload files is a image `imagick` or `gd` required. `imagick` is recommend and be priority of use

## Why this extension
To ensure upload large files on most browsers, but in addition to [plupload](https://github.com/moxiecode/plupload), all other plug-ins in use there is a little problem, such as [uploadify](http://www.uploadify.com/)


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

> Note: Read this [web tip /wiki](http://webtips.krajee.com/setting-composer-minimum-stability-application/) on setting the `minimum-stability` settings for your application's composer.json.

Either run

```
$ php composer.phar require ferguson/yii2-upload "dev-master"
```

or add

```
"ferguson/yii2-upload": "dev-master"
```

to the ```require``` section of your `composer.json` file.

## Usage
Once the extension is installed, simply modify your application configuration as follows:
```php
return [
    'modules' => [
        'upload' => [
            'class' => \ferguson\upload\Module::className(),
            'storage' => 'file', // which storage used, default `file` means file will be upload on server. other storages could be supported soon.
            'config' => [
                'dir' => '@webroot', // file upload directory, default `@webroot`, you can customer.
                'url' => '@web', // file uploaded host, default `@web`, you can customer.
                'resize' => [
                    'width' => '90',
                    'height' => '120',
                ], // thumb images size.
                'watermark' => [
                    'type' => ['font', 'image'], //null|string|array, which water type, single or both or none.
                    'position' => '',
                    //font water setting.
                    'font' => [
                        'ttf' => '',
                        'text' => '',//string, water text.
                        'size' => '10',// font size, default 10px
                    ],
                    //image water setting.
                    'image' => [
                        'src' => '', //string, water image path, absolute path.
                        'size' => ''
                    ], 
                ],
            ],
        ],
        //...
    ],
    //...
];
```

use in view pages.
```php
use ferguson\upload\Upload;

//Normal with ActiveForm & model
echo $form->field($model, 'logo')->widget(Upload::className(), Array $config = []);
```


## License

**yii2-upload** is released under the MIT License. See the bundled `LICENSE.md` for details.