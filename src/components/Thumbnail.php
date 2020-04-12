<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\imagine\Image;

/**
 * Description.
 *
 * @author Maxim Chichkanov
 */
class Thumbnail extends Component
{
    /**
     * @var string
     */
    public $source;

    /**
     * @var integer
     */
    public $height;

    /**
     * @var integer
     */
    public $width;

    /**
     * @var integer
     */
    public $quality;

    /**
     * @var string
     */
    public $pattern = '{source.directory}/thumbs/{source.filename}p{width}x{height}q{quality}.{source.extension}';

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $url;

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->source = Yii::getAlias($this->source);

        if (!($this->width || $this->height)) {
            throw new InvalidConfigException('Must specify the height or width of the image.');
        }

        $this->source = FileHelper::normalizePath($this->source);

        if (!file_exists($this->source)) {
            throw new InvalidConfigException('Image source file not found. Invalid path.');
        }

        $this->create();
        $this->publish();
    }

    /**
     * @return void
     */
    public function create()
    {
        if (!$this->has()) {
            $this->ensureDirectory();
            $this->createThumbnail();
        }
    }

    /**
     * @return void
     */
    public function publish()
    {
        list($path, $url) = Yii::$app->getAssetManager()->publish($this->getPath());
        return $this->url = $url;
    }

    /**
     * @return boolean
     */
    public function has()
    {
        return file_exists($this->getPath());
    }

    /**
     * @param array $config
     * @param array $options
     * @return string
     */
    public function img($options = [])
    {
        return Html::img($this->getUrl(), $options);
    }

    /**
     * @param string $path
     * @param string|array $style
     * @return array
     */
    public function cssStyle($style)
    {
        if (is_array($style)) {
            foreach ($style as $property => $value) {
                $style[$property] = strtr($value, $this->getVars());
            }
        } elseif (is_string($style)) {
            return strtr($style, $this->getVars());
        }

        return $style;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        return $this->path = FileHelper::normalizePath(
            strtr($this->pattern, $this->getVars())
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url ?? $this->publish();
    }

    /**
     * @return array
     */
    protected function getVars()
    {
        $sourceInfo = pathinfo($this->source);
        $styleUrl = "url('{$this->url}')";

        return [
            '{styleUrl}' => $styleUrl,
            '{path}' => $this->path,
            '{url}' => $this->url,

            '{quality}' => $this->quality,
            '{height}' => $this->height,
            '{width}' => $this->width,

            '{source.directory}' => $sourceInfo['dirname'],
            '{source.filename}' => $sourceInfo['filename'],
            '{source.basename}' => $sourceInfo['basename'],
            '{source.extension}' => isset($sourceInfo['extension'])
                ? $sourceInfo['extension'] : ''
        ];
    }

    /**
     * @return void
     */
    protected function ensureDirectory()
    {
        FileHelper::createDirectory(dirname($this->getPath()));
    }

    /**
     * @return void
     */
    protected function createThumbnail()
    {
        if ($this->isSvg()) {
            copy($this->source, $this->getPath());
        } else {
            $image = @Image::thumbnail($this->source, $this->width, $this->height);
            $image->save($this->getPath(), ['quality' => $this->quality]);
        }
    }

    /**
     * @return boolean
     */
    protected function isSvg()
    {
        return strtolower(pathinfo($this->source, PATHINFO_EXTENSION)) === 'svg';
    }
}
