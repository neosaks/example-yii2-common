<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\widgets\flags;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * Description.
 *
 * @author Maxim Chichkanov
 */
class Flag
{
    /**
     * @param string $code
     * @param string $ratio
     * @return boolean
     */
    public static function has($code, $ratio = '1x1')
    {
        return file_exists(static::getPath($code, $ratio));
    }

    /**
     * @param string $code
     * @param array $options
     * @param string $ratio
     * @return string
     */
    public static function img($code, $options = [], $ratio = '1x1')
    {
        return Html::img(static::getUrl($code, $ratio), $options);
    }

    /**
     * @param string $code
     * @param string|array $style
     * @param string $ratio
     * @return array
     */
    public static function cssStyle($code, $style, $ratio = '1x1')
    {
        if (is_array($style)) {
            foreach ($style as $property => $value) {
                $style[$property] = strtr($value, static::getVars($code, $ratio));
            }
        } elseif (is_string($style)) {
            return strtr($style, static::getVars($code, $ratio));
        }

        return $style;
    }

    /**
     * @param string $code
     * @param string $ratio
     * @return array
     */
    protected static function getVars($code, $ratio)
    {
        $assetPath = static::getPath($code, $ratio);
        $assetUrl = static::getUrl($code, $ratio);

        if (!file_exists($assetPath)) {
            return [];
        }

        $styleUrl = "url('$assetUrl')";

        return [
            '{styleUrl}' => $styleUrl,
            '{path}' => $assetPath,
            '{url}' => $assetUrl
        ];
    }

    /**
     * @param string $code
     * @param string $ratio
     * @return string
     */
    protected static function getPath($code, $ratio)
    {
        $assetManager = Yii::$app->getAssetManager();
        $bundle = $assetManager->getBundle(FlagAsset::class);

        $code = mb_strtolower($code);

        $assetPath = $assetManager->getAssetPath($bundle, "flags/$ratio/$code.svg");
        return FileHelper::normalizePath($assetPath);
    }

    /**
     * @param string $code
     * @param string $ratio
     * @return string
     */
    protected static function getUrl($code, $ratio)
    {
        $assetManager = Yii::$app->getAssetManager();
        $bundle = $assetManager->getBundle(FlagAsset::class);

        $code = mb_strtolower($code);

        return $assetManager->getAssetUrl($bundle, "flags/$ratio/$code.svg");
    }
}
