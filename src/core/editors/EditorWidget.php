<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\editors;

use common\core\editors\ace\AceWidget;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Description
 *
 * @author Maxim Chichkanov
 */
class EditorWidget extends \yii\widgets\InputWidget
{
    /**
     *
     */
    public $editors = [
        ImperaviWidget::class => [],
        AceWidget::class => []
    ];

    /**
     *
     */
    public $selector;

    /**
     *
     */
    public $selectors = [];

    /**
     *
     */
    public $callbacks = [];

    /**
     *
     */
    public function init()
    {
        parent::init();

        if ($this->selector === null) {
            $this->selector = '#' . $this->options['id'];
        }

        $this->selectors[$this->selector] = ['*'];

        $view = $this->getView();
        foreach ($this->editors as $editor => $config) {
            $callbacks = $editor::getCallbacks($view, ArrayHelper::merge($config, [
                'field' => $this->field,
                'model' => $this->model,
                'attribute' => $this->attribute,
                'name' => $this->name,
                'value' => $this->value,
                'options' => $this->options
            ]));

            if (!isset($callbacks['run'], $callbacks['destroy'])) {
                throw new NotSupportedException();
            }

            if (!($callbacks['run'] instanceof JsExpression)) {
                $callbacks['run'] = new JsExpression($callbacks['run']);
            }

            if (!($callbacks['destroy'] instanceof JsExpression)) {
                $callbacks['destroy'] = new JsExpression($callbacks['destroy']);
            }

            $this->callbacks[$editor::getEditorName()] = $callbacks;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->registerAssetBundle();

        if (!isset($this->field->parts['{input}'])) {
            $this->field->textarea();
        }

        return $this->field->parts['{input}'];
    }

    /**
     * Description.
     * @return void
     */
    public function registerAssetBundle()
    {
        $view = $this->getView();

        EditorAsset::register($view);

        $callbacks = Json::encode($this->callbacks);
        $selectors = Json::encode($this->selectors);

        $view->registerJs("new Editor($selectors, $callbacks);");
    }
}
