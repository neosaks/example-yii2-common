<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\widgets\flags;

use yii\web\AssetBundle;

/**
 * Description.
 *
 * @author Maxim Chichkanov
 */
class FlagAsset extends AssetBundle
{
    public $sourcePath = '@common/widgets/flags/assets';
    public $css = [
        'css/flag-icon.min.css'
    ];
    public $js = [
    ];
    public $depends = [
    ];
}
