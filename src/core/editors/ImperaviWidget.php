<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\editors;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use vova07\imperavi\Asset;
use vova07\imperavi\Widget;

/**
 * Description
 *
 * @author Maxim Chichkanov
 */
class ImperaviWidget extends Widget implements EditorInterface
{
    use PresetTrait;

    /**
     * @var string Description.
     */
    public $controller;

    /**
     *
     */
    protected $callbacks = [];

    /**
     * {@inheritdoc}
     */
    public function getPreset()
    {
        switch ($this->preset) {
            case 'default':
                return [
                    'settings' => [
                        'minHeight' => 400,
                        'imageUpload' => $this->createUrl('image-upload'),
                        'fileUpload' => $this->createUrl('file-upload'),
                        'fileDelete' => $this->createUrl('file-delete'),
                        'fileManagerJson' => $this->createUrl('files-get'),
                        'imageUpload' => $this->createUrl('image-upload'),
                        'imageDelete' => $this->createUrl('file-delete'),
                        'imageManagerJson' => $this->createUrl('images-get'),
                        'plugins' => [
                            'filemanager',
                            'imagemanager',
                            'definedlinks',
                            'fullscreen',
                            'fontfamily',
                            'fontsize',
                            'fontcolor',
                            'counter',
                            // 'clips',
                            'table'
                        ],
                    ],
                    'plugins' => [
                        'imagemanager' => 'vova07\imperavi\bundles\ImageManagerAsset',
                        'filemanager' => 'vova07\imperavi\bundles\FileManagerAsset'
                    ]
                ];
            
            default:
                return [];
        }
    }

    /**
     * Creates a URL.
     * @param string $action
     * @return string the created URL
     */
    protected function createUrl($action)
    {
        return Url::to((array) $this->controller ? $this->controller . '/' . $action : $action);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->register();
    }

    /**
     * {@inheritdoc}
     */
    protected function registerClientScripts()
    {
        $view = $this->getView();
        /** @var Asset $asset */
        $asset = Yii::$container->get(Asset::className());
        $asset = $asset::register($view);

        if (isset($this->settings['lang'])) {
            $asset->addLanguage($this->settings['lang']);
        }
        if (isset($this->settings['plugins'])) {
            $asset->addPlugins($this->settings['plugins']);
        }
        if (!empty($this->plugins)) {
            /** @var \yii\web\AssetBundle $bundle Asset bundle */
            foreach ($this->plugins as $plugin => $bundle) {
                $this->settings['plugins'][] = $plugin;
                $bundle::register($view);
            }
        }

        $selector = Json::encode($this->selector);
        $settings = !empty($this->settings) ? Json::encode($this->settings) : '';

        $editorName = self::getEditorName();

        $this->callbacks['run'] = "function(selector) {
            jQuery(selector).redactor($settings);
        }";
    }

    /**
     * Description.
     * @return array
     */
    public static function getCallbacks($view, $config)
    {
        unset($config['field']);

        self::begin($config);
        $widget = self::end();

        if (!isset($widget->callbacks['destroy'])) {
            $widget->callbacks['destroy'] = new JsExpression(
                "function(selector) {
                    jQuery(selector).redactor('core.destroy');
                }"
            );
        }

        return $widget->callbacks;
    }

    /**
     * Return edtior name.
     * @return string
     */
    public static function getEditorName()
    {
        return 'Imperavi';
    }

    /**
     * Return editor type.
     * @return string
     */
    public static function getEditorType()
    {
        return EditorInterface::EDITOR_TYPE_WYSIWYG;
    }
}
