<?php

/** @var \common\widgets\display\DisplayWidget $widget */
/** @var yii\web\View $this */

use yii\helpers\Html;

Html::addCssClass($widget->containerOptions, ['display-widget masonry']);

?>

<?= Html::beginTag('div', $widget->containerOptions); ?>
    <?= $widget->beforeContents(); ?>
    <div class="card-columns">
        <?php foreach ($widget->getModels() as $model) : ?>
            <?= $widget->beforeContent($model); ?>
            <?= $widget->renderCard($model); ?>
            <?= $widget->afterContent($model); ?>
        <?php endforeach; ?>
    </div>
    <?= $widget->afterContents(); ?>
    <?= $widget->renderPager(); ?>
<?= Html::endTag('div'); ?>
