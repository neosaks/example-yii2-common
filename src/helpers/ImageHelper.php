<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\helpers;

use yii\imagine\Image as Imagine;

/**
 * Description.
 *
 * @author Maxim Chichkanov <email>
 */
class ImageHelper
{
    /**
     * Description
     * @return string
     */
    public static function calcResolution($path)
    {
        /** @var BoxInterface $sourceBox */
        $sourceBox = Imagine::getImagine()->open($path)->getSize();
        return $sourceBox->getWidth() . 'x' . $sourceBox->getHeight();
    }
}
