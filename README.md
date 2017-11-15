yii2-froala-file-upload-module
==============================

This is a module for the Yii2 Framework which will help you upload files from froala editor and access them from the browser.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

1.add repository in composer.json

```
"repositories": [ 
       ...
       {
         "type": "git",
         "url": "https://github.com/wdc-admin/yii2-froala-file-upload-module.git"
       }
       ...
 ]
```
        
2.run        
```
~$ composer require --prefer-dist dmalchenko/yii2-froala-file-upload-module
```

or add

```
"dmalchenko/yii2-froala-file-upload-module": "^0.1"
```

to the require section of your `composer.json` file.


Basic Usage:
------

Add path aliases and url to your file store in the main config
```php
return [
    'aliases' => [
        '@storagePath' => '/path/to/upload/dir',
        '@storageUrl' => '/url/to/upload/dir',
    ],
];
```

Add action to the main controller
```php
use dmalchenko\fileUpload\actions\FroalaUploadAction;
 
class PageController extends Controller
{
    public function actions()
    {
        return [
            'upload' => [
                'class' => FroalaUploadAction::className(),
                'path' => Event::getUploadDir(), //path to uploads
                'url' => Event::getUploadUrl(), //url path for get files
                //'uploadOnlyImage' => false,
            ],
        ];
    }
    
    // ...
}
```


Need to add a param for FroalaEditorWidget
```php
<?= $form->field($model, 'comment')->widget(FroalaEditorWidget::class, [
    'clientOptions' => [
        //...
        'imageUploadURL' => \yii\helpers\Url::to(['froala-upload']),
        //..
    ]
])?>


```

