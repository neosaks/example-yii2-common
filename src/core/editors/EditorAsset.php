<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\editors;

use yii\web\AssetBundle;

/**
 * Editors Asset Bundle.
 *
 * @author Maxim Chichkanov <email>
 */
class EditorAsset extends AssetBundle
{
    public $sourcePath = '@common/core/editors/assets';
    public $css = [
    ];
    public $js = [
        'js/editors.js',
    ];
    public $depends = [
    ];
}
