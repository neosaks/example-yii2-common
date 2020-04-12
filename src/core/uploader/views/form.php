<?php
use common\widgets\bootstrap4\ActiveForm;
use common\widgets\bootstrap4\Html;
?>

<?php $form = ActiveForm::begin($formOptions); ?>

    <?= Html::beginTag('div', $containerOptions); ?>

        <div class="custom-file">
            <?= $form->field($model, $attribute, $fieldOptions)->fileInput($inputOptions); ?>
        </div>

        <div class="input-group-append">
            <?= Html::submitButton($submitContent, $submitOptions); ?>
        </div>

    <?= Html::endTag('div'); ?>

<?php ActiveForm::end(); ?>
