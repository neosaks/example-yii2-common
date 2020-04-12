<?php
use common\widgets\bootstrap4\Html;
?>

<?= Html::beginTag('div', $containerOptions); ?>

    <div class="custom-file">
        <?= $form->field($model, $attribute, $fieldOptions)->fileInput($inputOptions); ?>
    </div>

<?= Html::endTag('div'); ?>
