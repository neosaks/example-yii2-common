<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\uploader;

use yii\base\Event;

/**
 * This event class is used for Events triggered by the [[Uploader]] class.
 *
 * @author Maxim Chichkanov
 */
class UploadEvent extends Event
{
    /**
     * @var string
     */
    public $directory;
    /**
     * @var string
     */
    public $suffix;
    /**
     * @var string
     */
    public $filename;
    /**
     * @var string
     */
    public $path;
    /**
     * @var \yii\web\UploadedFile
     */
    public $file;
    /**
     * @var boolean
     */
    public $isValid = true;
}
