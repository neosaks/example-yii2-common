<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\editors\ace;

use yii\base\Widget;
use yii\web\JsExpression;
use common\core\editors\EditorInterface;

/**
 * Description
 *
 * @author Maxim Chichkanov
 */
class AceWidget extends Widget implements EditorInterface
{
    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->registerAssetBundle();
    }

    /**
     * Description.
     * @return void
     */
    public function registerAssetBundle()
    {
        AceAsset::register($this->getView());
    }

    /**
     * Return edtior name.
     * @return string
     */
    public static function getEditorName()
    {
        return 'Ace';
    }

    /**
     * Return editor type.
     * @return string
     */
    public static function getEditorType()
    {
        return EditorInterface::EDITOR_TYPE_CODE;
    }

    /**
     * Description.
     * @return array
     */
    public static function getCallbacks($view, $config)
    {
        AceAsset::register($view);

        return [
            'run' => new JsExpression('function(selector) { aceManager.init(selector); }'),
            'destroy' => new JsExpression('function(selector) { aceManager.destroy(selector); }')
        ];
    }
}
