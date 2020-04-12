<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\widgets\display;

use yii\web\AssetBundle;

/**
 * Description.
 *
 * @author Maxim Chichkanov
 */
class DisplayAsset extends AssetBundle
{
    public $sourcePath = '@common/widgets/display/assets';
    public $css = [
        'css/display.css'
    ];
    public $js = [
    ];
    public $depends = [
    ];
}
