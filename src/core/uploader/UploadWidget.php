<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\uploader;

use yii\bootstrap4\BootstrapAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ActiveField;
use yii\widgets\InputWidget;

/**
 * Description
 *
 * @author Maxim Chichkanov
 */
class UploadWidget extends InputWidget
{
    /**
     * @var Uploader
     */
    public $uploader;
    /**
     * @var ActiveForm
     */
    public $form;
    /**
     * @var array
     */
    public $urlAction = ['upload'];
    /**
     * @var array
     */
    public $urlParams = [];
    /**
     * @var array
     */
    public $formOptions = [];
    /**
     * @var array
     */
    public $fieldOptions = [];
    /**
     * @var array
     */
    public $inputOptions = [];
    /**
     * @var array
     */
    public $submitOptions = [];
    /**
     * @var array
     */
    public $containerOptions = [];
    /**
     * @var string
     */
    private $_attribute = 'files';

    /**
     *
     */
    public function init()
    {
        if (!$this->uploader) {
            $this->uploader = new Uploader();
        }

        if ($this->field instanceof ActiveField) {
            $validators = $this->model->getActiveValidators($this->attribute);

            $this->uploader->configure($validators);

            if (!$this->form) {
                $this->form = $this->field->form;
            }

            $this->form->options = array_merge_recursive($this->form->options, $this->formOptions);

            // hide submit button
            Html::addCssClass($this->submitOptions, ['class' => 'd-none']);
        }

        if (!$this->model) {
            $this->model = $this->uploader;
        }

        if (!$this->attribute) {
            $this->attribute = $this->_attribute;
        }

        if ($this->uploader->validator === Uploader::VALIDATOR_IMAGE) {
            $this->inputOptions['accept'] = 'image/*';
        }

        if ($this->uploader->maxFiles > 1) {
            $this->inputOptions['multiple'] = true;
            $this->attribute .= '[]';
        }

        if (!isset($this->formOptions['action'])) {
            $this->formOptions['action'] = $this->urlAction + $this->urlParams;
        }

        Html::addCssClass($this->submitOptions, ['widget' => 'btn btn-outline-secondary']);
        Html::addCssClass($this->containerOptions, ['widget' => 'input-group']);

        parent::init();
    }

    /**
     * Renders the widget.
     * @return string
     */
    public function run()
    {
        $this->registerAssetBundle();

        if ($this->form instanceof ActiveForm) {
            return $this->render('field', [
                'containerOptions' => $this->containerOptions,
                'fieldOptions' => $this->fieldOptions,
                'inputOptions' => $this->inputOptions,
                'attribute' => $this->attribute,
                'model' => $this->model,
                'form' => $this->form
            ]);
        }

        $submitContent = ArrayHelper::remove($this->submitOptions, 'content', 'Загрузить');

        return $this->render('form', [
            'containerOptions' => $this->containerOptions,
            'fieldOptions' => $this->fieldOptions,
            'inputOptions' => $this->inputOptions,
            'formOptions' => $this->formOptions,
            'submitOptions' => $this->submitOptions,
            'submitContent' => $submitContent,
            'attribute' => $this->attribute,
            'model' => $this->model
        ]);
    }

    /**
     * Register asset bundle
     * @return void
     */
    public function registerAssetBundle()
    {
        BootstrapAsset::register($this->getView());
    }
}
