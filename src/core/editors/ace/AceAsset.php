<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\editors\ace;

use yii\web\AssetBundle;

/**
 * Ace Editor Asset Bundle.
 *
 * @author Maxim Chichkanov <email>
 */
class AceAsset extends AssetBundle
{
    public $sourcePath = '@common/core/editors/ace/assets';
    public $css = [
    ];
    public $js = [
        'js/src-min/ace.js',
        'js/ace-manager.js'
    ];
    public $depends = [
    ];
}
