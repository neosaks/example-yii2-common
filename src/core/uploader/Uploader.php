<?php
namespace common\core\uploader;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\validators\Validator;
use yii\validators\FileValidator;
use yii\validators\ImageValidator;
use yii\validators\RequiredValidator;

/**
 * Description.
 */
class Uploader extends Model
{
    const EVENT_BEFORE_UPLOAD = 'beforeUpload';
    const EVENT_AFTER_UPLOAD = 'afterUpload';

    const VALIDATOR_IMAGE = 'image';
    const VALIDATOR_FILE = 'file';

    /**
     * @var string
     */
    public $directory = '@common/uploads/shared';
    /**
     * @var string
     */
    public $label = 'Файлы';
    /**
     * @var string
     */
    public $validator = 'file';

    /**
     * @see \yii\validators\RequiredValidator
     */
    public $required = false;
    /**
     * @see \yii\validators\FileValidator
     */
    public $extensions = null;
    /**
     * @see \yii\validators\FileValidator
     */
    public $mimeTypes = null;
    /**
     * @see \yii\validators\FileValidator
     */
    public $minSize = null;
    /**
     * @see \yii\validators\FileValidator
     */
    public $maxSize = null;
    /**
     * @see \yii\validators\FileValidator
     */
    public $minFiles = null;
    /**
     * @see \yii\validators\FileValidator
     */
    public $maxFiles = null;
    /**
     * @see \yii\validators\FileValidator
     */
    public $checkExtensionByMimeType = true;
    /**
     * @see \yii\validators\ImageValidator
     */
    public $minWidth = null;
    /**
     * @see \yii\validators\ImageValidator
     */
    public $maxWidth = null;
    /**
     * @see \yii\validators\ImageValidator
     */
    public $minHeight = null;
    /**
     * @see \yii\validators\ImageValidator
     */
    public $maxHeight = null;
    /**
     * @var string
     */
    public $message;

    /**
     * @var UploadedFile[]
     */
    public $files;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'files' => $this->label
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->minWidth || $this->maxWidth || $this->minHeight || $this->maxHeight) {
            $this->validator = self::VALIDATOR_IMAGE;
        }

        $maxSize = $this->getSizeLimit();
        $maxFiles = (int) ini_get('max_file_uploads');

        if (!$this->maxSize || $this->maxSize > $maxSize) {
            $this->maxSize = $maxSize;
        }

        if (!$this->maxFiles || $this->maxFiles > $maxFiles) {
            $this->maxFiles = $maxFiles;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rule = [
            'files', $this->validator,
            'extensions' => $this->extensions,
            'mimeTypes' => $this->mimeTypes,
            'minSize' => $this->minSize,
            'maxSize' => $this->maxSize,
            'minFiles' => $this->minFiles,
            'maxFiles' => $this->maxFiles,
            'message' => $this->message,
            'checkExtensionByMimeType' => $this->checkExtensionByMimeType
        ];

        if ($this->validator === self::VALIDATOR_IMAGE) {
            $rule['minWidth'] = $this->minWidth;
            $rule['maxWidth'] = $this->maxWidth;
            $rule['minHeight'] = $this->minHeight;
            $rule['maxHeight'] = $this->maxHeight;
        }

        $rules = [$rule];

        if ($this->required) {
            $rules[] = ['files', 'required'];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function load($data, $formName = null)
    {
        if (($loaded = parent::load($data, $formName))) {
            $this->files = UploadedFile::getInstances($this, 'files');
        }

        return $loaded;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $files = $this->files;

        if (count($this->files) && ($this->maxFiles == 1)) {
            $this->files = $this->files[0];
        }

        $result = parent::validate($attributeNames, $clearErrors);

        $this->files = $files;

        return $result;
    }

    /**
     * Description.
     *
     * @return boolean
     */
    public function save()
    {
        return $this->upload();
    }

    /**
     * Description.
     *
     * @param boolean $autload
     * @return boolean
     */
    public function upload()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->files as $file) {
            $directory = $this->getDirectory();
            $suffix = $this->getSuffix($file);
            $filename = $this->getFilename($file);

            $path = $this->joinPaths($directory, $suffix, $filename);

            FileHelper::createDirectory(dirname($path));

            $isValid = $this->beforeUpload($directory, $suffix, $filename, $file);
            if ($isValid && $file->saveAs($path)) {
                $this->afterUpload($directory, $suffix, $filename, $file);
            } else {
                // @todo add error in $this->errors
            }
        }

        return true;
    }

    /**
     * Description.
     *
     * @param Validator[] $validators
     * @return void
     */
    public function configure($validators)
    {
        foreach ($validators as $validator) {
            if ($validator instanceof FileValidator) {
                $this->extensions = $validator->extensions;
                $this->mimeTypes = $validator->mimeTypes;
                $this->minSize = $validator->minSize;
                $this->maxSize = $validator->maxSize;
                $this->maxFiles = $validator->maxFiles;
                $this->minFiles = $validator->minFiles;
                $this->message = $validator->message;
            }

            if ($validator instanceof ImageValidator) {
                $this->validator = Uploader::VALIDATOR_IMAGE;
                $this->maxWidth = $validator->maxWidth;
                $this->minWidth = $validator->minWidth;
                $this->maxHeight = $validator->maxHeight;
                $this->minHeight = $validator->minHeight;
            }

            if ($validator instanceof RequiredValidator) {
                $this->required = true;
            }
        }

        $this->init();
    }

    /**
     * Description.
     *
     * @return string
     */
    public function getDirectory()
    {
        return FileHelper::normalizePath(Yii::getAlias($this->directory));
    }

    /**
     * Description.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getSuffix(UploadedFile $file)
    {
        return Yii::$app->user->id . date('mY');
    }

    /**
     * Description.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getFilename(UploadedFile $file)
    {
        $extension = $file->getExtension();
        $randonString = Yii::$app->security->generateRandomString();
        return $randonString . '_' . time() . ($extension ? ".$extension" : '');
    }

    /**
     * Description.
     *
     * @param string $directory
     * @param string $suffix
     * @param string $filename
     * @param UploadedFile $file
     * @return boolean
     */
    protected function beforeUpload($directory, $suffix, $filename, $file)
    {
        $event = new UploadEvent([
            'directory' => $directory,
            'suffix' => $suffix,
            'filename' => $filename,
            'file' => $file
        ]);

        $this->trigger(self::EVENT_BEFORE_UPLOAD, $event);

        return $event->isValid;
    }

    /**
     * Description.
     *
     * @param string $directory
     * @param string $filename
     * @param string $path
     * @param UploadedFile $file
     * @return void
     */
    protected function afterUpload($directory, $suffix, $filename, $file)
    {
        $event = new UploadEvent([
            'directory' => $directory,
            'suffix' => $suffix,
            'filename' => $filename,
            'file' => $file
        ]);

        $this->trigger(self::EVENT_AFTER_UPLOAD, $event);
    }

    /**
     * Description.
     *
     * @return string
     */
    protected function joinPaths(...$parts)
    {
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Converts php.ini style size to bytes.
     *
     * @param string $sizeStr
     * @return integer
     * @see FileValidator
     */
    protected function sizeToBytes($sizeString)
    {
        switch (substr($sizeString, -1)) {
            case 'M':
            case 'm':
                return (int) $sizeString * 1048576;

            case 'K':
            case 'k':
                return (int) $sizeString * 1024;

            case 'G':
            case 'g':
                return (int) $sizeString * 1073741824;

            default:
                return (int) $sizeString;
        }
    }

    /**
     * Returns the maximum size allowed for uploaded files.
     *
     * This is determined based on four factors:
     *
     * - 'upload_max_filesize' in php.ini
     * - 'post_max_size' in php.ini
     * - 'MAX_FILE_SIZE' hidden field
     * - [[maxSize]]
     *
     * @return integer the size limit for uploaded files.
     * @see FileValidator
     */
    protected function getSizeLimit()
    {
        // Get the lowest between post_max_size and upload_max_filesize, log a warning if the first is < than the latter
        $limit = $this->sizeToBytes(ini_get('upload_max_filesize'));
        $postLimit = $this->sizeToBytes(ini_get('post_max_size'));
        if ($postLimit > 0 && $postLimit < $limit) {
            Yii::warning('PHP.ini\'s \'post_max_size\' is less than \'upload_max_filesize\'.', __METHOD__);
            $limit = $postLimit;
        }
        if ($this->maxSize !== null && $limit > 0 && $this->maxSize < $limit) {
            $limit = $this->maxSize;
        }
        if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit) {
            $limit = (int) $_POST['MAX_FILE_SIZE'];
        }

        return $limit;
    }
}
