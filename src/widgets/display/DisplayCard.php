<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\widgets\display;

use Yii;
use common\components\Thumbnail;
use common\widgets\buttons\ButtonsWidget;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Description
 *
 * @author Maxim Chichkanov
 */
class DisplayCard extends \yii\base\Widget
{
    /**
     * @var DisplayWidget
     */
    public $display;

    /**
     * @var object
     */
    public $model;

    /**
     * @var string
     */
    public $template = 'default';

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string
     */
    public $header;

    /**
     * @var array
     */
    public $headerOptions = [];

    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $titleOptions = [];

    /**
     * @var string
     */
    public $text;

    /**
     * @var array
     */
    public $textOptions = [];

    /**
     * @var string
     */
    public $body = '';

    /**
     * @var array
     */
    public $bodyOptions = [];

    /**
     * @var string
     */
    public $footer;

    /**
     * @var array
     */
    public $footerOptions = ['class' => 'text-muted'];

    /**
     * @var string
     */
    public $content;

    /**
     * @var array
     */
    public $contentOptions = [];

    /**
     * @var ImageInterface
     */
    public $image;

    /**
     * @var array
     */
    public $imageOptions = [];

    /**
     * @var array
     */
    public $buttons = [];

    /**
     * @var array
     */
    public $buttonOptions = [];

    /**
     * @var array templates
     */
    public $templates = [
        'default' =>
            '{beginHeader}{header}{endHeader}' .
            '{image}' .
            '{beginBody}{beginTitle}{title}{endTitle}{beginText}{text}{endText}{buttons}{endBody}' .
            '{beginFooter}{footer}{endFooter}'
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!$this->display instanceof DisplayWidget) {
            throw new InvalidConfigException();
        }

        $options = $this->display->getOptions('cardOptions', [], $this->model);
        $headerOptions = $this->display->getOptions('cardHeaderOptions', [], $this->model);
        $titleOptions = $this->display->getOptions('cardTitleOptions', [], $this->model);
        $textOptions = $this->display->getOptions('cardTextOptions', [], $this->model);
        $bodyOptions = $this->display->getOptions('cardBodyOptions', [], $this->model);
        $footerOptions = $this->display->getOptions('cardFooterOptions', [], $this->model);
        $imageOptions = $this->display->getOptions('cardImageOptions', [], $this->model);

        $this->options = array_merge_recursive($this->options, $options);
        $this->headerOptions = array_merge_recursive($this->headerOptions, $headerOptions);
        $this->titleOptions = array_merge_recursive($this->titleOptions, $titleOptions);
        $this->textOptions = array_merge_recursive($this->textOptions, $textOptions);
        $this->bodyOptions = array_merge_recursive($this->bodyOptions, $bodyOptions);
        $this->footerOptions = array_merge_recursive($this->footerOptions, $footerOptions);
        $this->imageOptions = array_merge_recursive($this->imageOptions, $imageOptions);

        Html::addCssClass($this->options, ['widget' => 'card shadow']);
        Html::addCssClass($this->headerOptions, ['widget' => 'card-header']);
        Html::addCssClass($this->titleOptions, ['widget' => 'card-title']);
        Html::addCssClass($this->textOptions, ['widget' => 'card-text']);
        Html::addCssClass($this->bodyOptions, ['widget' => 'card-body']);
        Html::addCssClass($this->footerOptions, ['widget' => 'card-footer']);
        Html::addCssClass($this->imageOptions, ['widget' => 'card-img-top']);

        if (isset($this->templates[$this->template])) {
            $this->template = $this->templates[$this->template];
        }

        if ($this->title === null) {
            $this->title = $this->display->getTitle($this->model);
        }

        if ($this->text === null) {
            $this->text = $this->display->getDescription($this->model);
        }

        if ($this->footer === null) {
            $footerText = $this->display->getFooterText($this->model);
            $footerUrl = $this->display->getFooterUrl($this->model);
            $footerUrlOptions = ArrayHelper::remove($this->footerOptions, 'urlOptions', []);
            $this->footer = $footerUrl ? Html::a($footerText, $footerUrl, $footerUrlOptions) : $footerText;
        }

        $buttonOptions = $this->display->getOptions('cardButtonOptions', [], $this->model);
        $this->buttonOptions = array_merge_recursive($this->buttonOptions, $buttonOptions);

        if (!$this->buttons) {
            $this->buttons = $this->display->getOptions('cardButtons', [], $this->model);
        }

        if (!$this->buttons) {
            $this->initDefaultButtons();
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->renderCard();
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderCard()
    {
        $before = $this->display->beforeCard($this->model);

        $elements = $this->getCustomElements() + $this->getDefaultElements();
        $content = strtr($this->template, $elements);

        $tag = ArrayHelper::remove($this->options, 'tag', 'div');
        $card = Html::tag($tag, $content, $this->options);

        $after = $this->display->afterCard($this->model);

        return $before . $card . $after;
    }

    /**
     * Description.
     *
     * @return array
     */
    public function getDefaultElements()
    {
        return $this->getCustomElements() + [
            '{beginHeader}' => $this->renderBeginHeader(),
            '{endHeader}' => $this->renderEndHeader(),
            '{beginTitle}' => $this->renderBeginTitle(),
            '{endTitle}' => $this->renderEndTitle(),
            '{beginText}' => $this->renderBeginText(),
            '{endText}' => $this->renderEndText(),
            '{beginBody}' => $this->renderBeginBody(),
            '{endBody}' => $this->renderEndBody(),
            '{beginFooter}' => $this->renderBeginFooter(),
            '{endFooter}' => $this->renderEndFooter(),
            '{beginContent}' => $this->renderBeginContent(),
            '{endContent}' => $this->renderEndContent(),

            '{header}' => $this->renderHeader(),
            '{title}' => $this->renderTitle(),
            '{text}' => $this->renderText(),
            '{body}' => $this->renderBody(),
            '{footer}' => $this->renderFooter(),
            '{content}' => $this->renderContent(),

            '{image}' => $this->renderImage(),
            '{buttons}' => $this->renderButtons()
        ];
    }

    /**
     * Description.
     *
     * @return array
     */
    public function getCustomElements()
    {
        return [];
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderBeginHeader()
    {
        $content = $this->display->beforeCardHeader($this->model);
        $options = $this->headerOptions;

        if ($this->header !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return $content . Html::beginTag($tag, $options);
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderEndHeader()
    {
        $content = $this->display->afterCardHeader($this->model);
        $options = $this->headerOptions;

        if ($this->header !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return Html::endTag($tag) . $content;
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderBeginTitle()
    {
        $content = $this->display->beforeCardTitle($this->model);
        $options = $this->titleOptions;

        if ($this->title !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'h5');
            return $content . Html::beginTag($tag, $options);
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderEndTitle()
    {
        $content = $this->display->afterCardTitle($this->model);
        $options = $this->titleOptions;

        if ($this->title !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'h5');
            return Html::endTag($tag) . $content;
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderBeginText()
    {
        $content = $this->display->beforeCardText($this->model);
        $options = $this->textOptions;

        if ($this->text !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'p');
            return $content . Html::beginTag($tag, $options);
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderEndText()
    {
        $content = $this->display->afterCardText($this->model);
        $options = $this->textOptions;

        if ($this->text !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'p');
            return Html::endTag($tag) . $content;
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderBeginBody()
    {
        $content = $this->display->beforeCardBody($this->model);
        $options = $this->bodyOptions;

        if ($this->body !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return $content . Html::beginTag($tag, $options);
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderEndBody()
    {
        $content = $this->display->afterCardBody($this->model);
        $options = $this->bodyOptions;

        if ($this->body !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return Html::endTag($tag) . $content;
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderBeginFooter()
    {
        $content = $this->display->beforeCardFooter($this->model);
        $options = $this->footerOptions;

        if ($this->footer !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return $content . Html::beginTag($tag, $options);
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderEndFooter()
    {
        $content = $this->display->afterCardFooter($this->model);
        $options = $this->footerOptions;

        if ($this->footer !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return Html::endTag($tag) . $content;
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderBeginContent()
    {
        $content = $this->display->beforeCardContent($this->model);
        $options = $this->contentOptions;

        if ($this->content !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return $content . Html::beginTag($tag, $options);
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderEndContent()
    {
        $content = $this->display->afterCardContent($this->model);
        $options = $this->contentOptions;

        if ($this->content !== null) {
            $tag = ArrayHelper::remove($options, 'tag', 'div');
            return Html::endTag($tag) . $content;
        }

        return $content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderHeader()
    {
        return $this->header;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderTitle()
    {
        return $this->title;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderText()
    {
        return $this->text;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderBody()
    {
        return $this->body;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderFooter()
    {
        return $this->footer;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderContent()
    {
        return $this->content;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderImage()
    {
        if (!$this->display->hasImage($this->model)) {
            return '';
        }

        $image = $this->display->getImage($this->model);

        return Yii::createObject([
            'class' => Thumbnail::class,
            'source' => $image->getPath(),
            'height' => 400
        ])->img($this->imageOptions);
    }

    /**
     * Description.
     *
     * @return string
     */
    public function renderButtons()
    {
        return ButtonsWidget::widget([
            'buttons' => $this->buttons,
            'options' => $this->buttonOptions
        ]);
    }

    /**
     * Description.
     *
     * @return void
     */
    protected function initDefaultButtons()
    {
        $this->buttons = [
            'detail' => [
                'label' => $this->display->getOptions('readMoreText', 'Подробнее &raquo', $this->model),
                'options' => $this->display->getOptions('readMoreOptions', [
                    'url' => $this->display->getDeatilUrl($this->model),
                    'class' => 'btn btn-secondary'
                ], $this->model),
                'encodeLabel' => false
            ]
        ];
    }
}
