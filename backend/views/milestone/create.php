<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Milestone */

$this->title = 'Create Milestone';
?>
<style>
    .form-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .form-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-back {
        padding: 10px 24px;
        background: rgba(255, 255, 255, 0.15);
        color: #E8D4BC;
        border: 2px solid #E8D4BC;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        color: #E8D4BC;
        text-decoration: none;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        max-width: 800px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        color: #2D1F13;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 15px;
        color: #2D1F13;
        background: white;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #967259;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .color-input-wrapper {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .color-picker {
        width: 60px;
        height: 44px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .color-picker:hover {
        border-color: #967259;
        transform: scale(1.05);
    }

    .color-text {
        flex: 1;
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkbox-wrapper input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #967259;
    }

    .checkbox-wrapper label {
        margin: 0 !important;
        cursor: pointer;
        text-transform: none;
        letter-spacing: normal;
        font-weight: 500;
    }

    .help-block {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 6px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 35px;
        padding-top: 25px;
        border-top: 2px solid #E8D4BC;
    }

    .btn-submit {
        padding: 14px 32px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(150, 114, 89, 0.4);
    }

    .btn-reset {
        padding: 14px 32px;
        background: white;
        color: #967259;
        border: 2px solid #967259;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-reset:hover {
        background: #967259;
        color: white;
        transform: translateY(-2px);
    }

    .field-milestone-is_active,
    .field-milestone-sort_order {
        width: 48%;
        display: inline-block;
        vertical-align: top;
    }

    .field-milestone-is_active {
        margin-right: 4%;
    }

    .number-input {
        width: 150px;
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h1>
            <span>➕</span>
            Create New Milestone
        </h1>
        <?= Html::a('<span>←</span> Back to List', ['index'], ['class' => 'btn-back']) ?>
    </div>

    <div class="form-card">
        <?php $form = ActiveForm::begin([
            'id' => 'milestone-form',
            'options' => ['class' => 'milestone-form'],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'help-block'],
            ],
        ]); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Enter milestone name']) ?>

        <?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'Enter milestone description (optional)']) ?>

        <div class="form-group field-milestone-color">
            <?= Html::activeLabel($model, 'color', ['class' => 'form-label']) ?>
            <div class="color-input-wrapper">
                <?= Html::activeInput('color', $model, 'color', [
                    'class' => 'color-picker',
                    'id' => 'color-picker'
                ]) ?>
                <?= Html::activeTextInput($model, 'color', [
                    'class' => 'form-control color-text',
                    'id' => 'color-text',
                    'placeholder' => '#967259',
                    'maxlength' => 7
                ]) ?>
            </div>
            <?= Html::error($model, 'color', ['class' => 'help-block']) ?>
        </div>

        <div class="form-group field-milestone-sort_order">
            <?= $form->field($model, 'sort_order')->textInput([
                'type' => 'number',
                'class' => 'form-control number-input',
                'placeholder' => '0'
            ]) ?>
        </div>

        <div class="form-group field-milestone-is_active">
            <?= Html::activeLabel($model, 'is_active', ['class' => 'form-label']) ?>
            <div class="checkbox-wrapper">
                <?= Html::activeCheckbox($model, 'is_active', [
                    'label' => 'Mark as active',
                    'uncheck' => 0,
                ]) ?>
            </div>
            <?= Html::error($model, 'is_active', ['class' => 'help-block']) ?>
        </div>

        <div class="form-actions">
            <?= Html::resetButton('Reset Form', ['class' => 'btn-reset']) ?>
            <?= Html::submitButton('Create Milestone', ['class' => 'btn-submit']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
// Sync color picker with text input
document.getElementById('color-picker').addEventListener('input', function(e) {
    document.getElementById('color-text').value = e.target.value.toUpperCase();
});

document.getElementById('color-text').addEventListener('input', function(e) {
    let value = e.target.value;
    if (value.match(/^#[0-9A-Fa-f]{6}$/)) {
        document.getElementById('color-picker').value = value;
    }
});
</script>
