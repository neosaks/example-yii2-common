<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\widgets\display;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\data\DataProviderInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use common\widgets\bootstrap4\LinkPager;
use common\interfaces\entity\ImageInterface;
use common\interfaces\OptionsInterface;
use common\behaviors\OptionsBehavior;
use common\helpers\Framework;
use common\interfaces\entity\ImageBoxInterface;

/**
 * Description
 *
 * @author Maxim Chichkanov
 */
class DisplayWidget extends \yii\base\Widget
{
    /**
     * @var string
     */
    public $template = 'grid';
    /**
     * @var array
     */
    public $blocks = [];
    /**
     * @var array
     */
    public $options = [];
    /**
     * @var object[]
     */
    public $models = [];
    /**
     * @var object[]
     */
    public $customModels = [];
    /**
     * @var \yii\data\DataProviderInterface
     */
    public $dataProvider;
    /**
     * @var boolean|array the configuration for the pager widget. By default, [[LinkPager]] will be
     * used to render the pager. You can use a different widget class by configuring the "class" element.
     * Note that the widget must support the `pagination` property which will be populated with the
     * [[\yii\data\BaseDataProvider::pagination|pagination]] value of the [[dataProvider]] and will overwrite this value.
     */
    public $pager = [];
    /**
     * @var callable a callback that creates a button URL using the specified model information.
     * The signature of the callback should be the same as that of [[createUrl()]]
     *
     * ```php
     * function (mixed $model, DisplayWidget $this) {
     *     // return string;
     * }
     * ```
     *
     * If this property is not set, button URLs will be created using [[createUrl()]].
     */
    public $detailUrl;
    /**
     * @var array
     */
    public $containerOptions = [];
    /**
     * @var string
     */
    public $optionsPrefix = 'widgets.display.';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->registerAssetBundle();

        return $this->render($this->template, [
            'widget' => $this
        ]);
    }

    /**
     * @return object[]
     */
    public function getModels()
    {
        if (!$this->models) {
            $this->models = $this->getDataProvider()->getModels();
        }

        return $this->models + $this->customModels;
    }

    /**
     * @return DataProviderInterface
     */
    public function getDataProvider()
    {
        if (!$this->dataProvider instanceof DataProviderInterface) {
            throw new InvalidConfigException(
                'The "dataProvider" property must be an instance of a class' .
                ' that implements the DataProviderInterface or its subclasses.'
            );
        }

        return $this->dataProvider;
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @param object $model
     * @return mixed
     */
    public function getOptions($key, $defaultValue = null, $model = null)
    {
        if ($model && $this->ensureOptionsInterface($model)) {
            /** @var OptionsInterface $model */
            return $model->getOptions($this->optionsPrefix . $key, $defaultValue);
        }

        return isset($this->options[$key]) ? $this->options[$key] : $defaultValue;
    }

    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        if ($this->pager === false) {
            return;
        }

        $pagination = $this->getDataProvider()->getPagination();
        if ($pagination === false || $this->getDataProvider()->getCount() <= 0) {
            return '';
        }
        /** @var LinkPager $class */
        $pager = $this->pager;
        $class = ArrayHelper::remove($pager, 'class', LinkPager::class);
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();

        return $class::widget($pager);
    }

    /**
     * @param object $model
     * @return string
     */
    public function renderCard($model)
    {
        return DisplayCard::widget([
            'display' => $this,
            'model' => $model
        ]);
    }

    /**
     * @return void
     */
    public function registerAssetBundle()
    {
        DisplayAsset::register($this->getView());
    }

    /**
     * @param object $model
     * @return array|null
     */
    public function getDeatilUrl($model)
    {
        if (is_callable($this->detailUrl)) {
            return call_user_func($this->detailUrl, $model, $this);
        }
    }

    /**
     * @param object $model
     * @return string
     */
    public function getTitle($model)
    {
        return $this->getOptions('title', null, $model);
    }

    /**
     * @param object $model
     * @return string
     */
    public function getDescription($model)
    {
        return $this->getOptions('description', null, $model);
    }

    /**
     * @param object $model
     * @return boolean|null
     */
    public function hasImage($model)
    {
        if ($model instanceof ImageBoxInterface) {
            return $model->hasImage();
        }
    }

    /**
     * @param object $model
     * @return ImageInterface|null
     */
    public function getImage($model)
    {
        if ($model instanceof ImageBoxInterface) {
            return $model->fetchImage();
        }
    }

    /**
     * @param $model
     * @return string
     */
    public function getFooterText($model)
    {
        return $this->getOptions('footerText', null, $model);
    }

    /**
     * @param object $model
     * @return array
     */
    public function getFooterUrl($model)
    {
        return $this->getOptions('footerUrl', '', $model);
    }

    /** Template Block's */

    /**
     * Description.
     *
     * @return string
     */
    public function beforeContents()
    {
        $content = ArrayHelper::getValue($this->blocks, 'beforeContents', '');

        if (is_string($content)) {
            return $content;
        }

        $tag = ArrayHelper::getValue($content, 'tag', 'div');
        $options = ArrayHelper::getValue($content, 'options', []);
        $content = ArrayHelper::getValue($content, 'content', '');

        return Html::tag($tag, $content, $options);
    }

    /**
     * Description.
     *
     * @return string
     */
    public function afterContents()
    {
        $content = ArrayHelper::getValue($this->blocks, 'afterContents', '');

        if (is_string($content)) {
            return $content;
        }

        $tag = ArrayHelper::getValue($content, 'tag', 'div');
        $options = ArrayHelper::getValue($content, 'options', []);
        $content = ArrayHelper::getValue($content, 'content', '');

        return Html::tag($tag, $content, $options);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeContent($model)
    {
        return $this->getOptions('beforeContent', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterContent($model)
    {
        return $this->getOptions('afterContent', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCard($model)
    {
        return $this->getOptions('beforeCard', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCard($model)
    {
        return $this->getOptions('afterCard', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardRow($model)
    {
        return $this->getOptions('beforeCardRow', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardRow($model)
    {
        return $this->getOptions('afterCardRow', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardColumn($model)
    {
        return $this->getOptions('beforeCardColumn', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardColumn($model)
    {
        return $this->getOptions('afterCardColumn', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardHeader($model)
    {
        return $this->getOptions('beforeCardHeader', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardHeader($model)
    {
        return $this->getOptions('afterCardHeader', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardTitle($model)
    {
        return $this->getOptions('beforeCardTitle', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardTitle($model)
    {
        return $this->getOptions('afterCardTitle', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardText($model)
    {
        return $this->getOptions('beforeCardText', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardText($model)
    {
        return $this->getOptions('afterCardText', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardBody($model)
    {
        return $this->getOptions('beforeCardBody', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardBody($model)
    {
        return $this->getOptions('afterCardBody', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardFooter($model)
    {
        return $this->getOptions('beforeCardFooter', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardFooter($model)
    {
        return $this->getOptions('afterCardFooter', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function beforeCardContent($model)
    {
        return $this->getOptions('beforeCardContent', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return string
     */
    public function afterCardContent($model)
    {
        return $this->getOptions('afterCardContent', '', $model);
    }

    /**
     * Description.
     *
     * @param object $model
     * @return boolean
     */
    public function ensureOptionsInterface($model)
    {
        return $model instanceof OptionsInterface || $model instanceof Component
            && Framework::isAttached(OptionsBehavior::class, $model);
    }
}
