<?php

namespace wdc\fileUpload\actions;

use Yii;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class FroalaUploadAction
 *
 * UploadAction for images and files.
 *
 * forked lav45/yii2-file-upload-module
 *
 * Usage:
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'froala-upload' => [
 *             'class' => 'wdc\fileUpload\actions\FroalaUploadAction',
 *             'url' => '/statics',
 *             'path' => '@webroot/statics',
 *             'uploadOnlyImage' => false,
 *             'validatorOptions' => [
 *                 'maxSize' => 40000
 *             ]
 *         ]
 *     ];
 * }
 * ```
 */
class FroalaUploadAction extends Action
{
    /**
     * @var string Variable's name that Imperavi Redactor sent upon image/file upload.
     */
    public $uploadParam = 'file';
    /**
     * @var array validator options for \yii\validators\FileValidator or \yii\validators\ImageValidator
     */
    public $validatorOptions = [];
    /**
     * @var array|\Closure
     */
    public $afterRun;
    /**
     * @var array|\Closure
     */
    public $createFileName;
    /**
     * @var string Path to directory where files will be uploaded
     */
    public $path;
    /**
     * @var string URL path to directory where files will be uploaded
     */
    public $url = '@web/assets/upload';
    /**
     * @var string Model validator name
     */
    private $validator = 'image';

    /**
     * Initializes the object.
     */
    public function init()
    {
        parent::init();

        $this->setPath($this->path);
        $this->setUrl($this->url);
    }

    /**
     * @param string $path
     * @throws InvalidConfigException
     */
    public function setPath($path)
    {
        if (empty($path)) {
            throw new InvalidConfigException('The "path" attribute must be set.');
        }

        $path = Yii::getAlias($path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!FileHelper::createDirectory($path)) {
            throw new InvalidCallException("Directory specified in 'path' attribute doesn't exist or cannot be created.");
        }

        $this->path = $path;
    }

    /**
     * @param string $url
     * @throws InvalidConfigException
     */
    public function setUrl($url)
    {
        if (empty($url)) {
            throw new InvalidConfigException('The "url" attribute must be set.');
        }

        $url = Yii::getAlias($url);
        $this->url = rtrim($url, '/');
    }

    /**
     * @param boolean $flag
     */
    public function setUploadOnlyImage($flag)
    {
        $this->validator = $flag === true ? 'image' : 'file';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;

        if (Yii::$app->getRequest()->getIsPost() === false) {
            throw new BadRequestHttpException('Only POST is allowed');
        }

        $file = UploadedFile::getInstanceByName($this->uploadParam);

        $model = new DynamicModel(['file' => $file]);
        $model->addRule('file', $this->validator, $this->validatorOptions);

        if ($model->validate() === false) {
            $result = ['error' => $model->getFirstError('file')];
        } else {
            $response->getHeaders()->set('Vary', 'Accept');
            $file->name = $this->createFileName($file);

            $result = [
                'link' => $this->url . '/' . $file->name,
            ];

            if ($file->saveAs($this->path . '/' . $file->name) === false) {
                $result = ['error' => 'Failed to load file'];
                @unlink($file->tempName);
            }
        }

        if (is_callable($this->afterRun)) {
            $result = call_user_func($this->afterRun, $result);
        }

        return $result;
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function createFileName(UploadedFile $file)
    {
        $fileExtension = ($file->getExtension() ? '.' . $file->getExtension() : '');
        if ($this->createFileName === null) {
            do {
                $file_name = uniqid() . $fileExtension;
            } while (file_exists($this->path . '/' . $file_name));
        } else {
            $file_name = call_user_func($this->createFileName, $fileExtension, $this->path);
        }

        return $file_name;
    }
}
