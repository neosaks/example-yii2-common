<?php

/** @var \common\widgets\display\DisplayWidget $widget */
/** @var yii\web\View $this */

use yii\helpers\Html;
?>

<?= Html::beginTag('div', $widget->containerOptions); ?>
    <?= $widget->beforeContents(); ?>
    <div class="container">
        <div class="row">
            <?php foreach ($widget->getModels() as $model) : ?>
                <div class="col-md-4 mt-3">
                    <?= $widget->beforeContent($model); ?>
                    <?= $widget->renderCard($model); ?>
                    <?= $widget->afterContent($model); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?= $widget->afterContents(); ?>
    <?= $widget->renderPager(); ?>
<?= Html::endTag('div'); ?>
