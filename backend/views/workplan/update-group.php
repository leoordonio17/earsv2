<?php

use yii\helpers\Html;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\WorkplanGroup */

$this->title = 'Update Workplan Group';
?>
<style>
    .update-group-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
    }

    .btn-back {
        padding: 10px 24px;
        background: rgba(232, 212, 188, 0.2);
        color: #E8D4BC;
        border: 2px solid #E8D4BC;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: #E8D4BC;
        color: #2D1F13;
        text-decoration: none;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 15px;
    }

    .form-control:focus {
        border-color: #967259;
        outline: none;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .btn-submit {
        padding: 12px 28px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
    }
</style>

<div class="update-group-container">
    <div class="page-header">
        <h1>✏️ <?= Html::encode($this->title) ?></h1>
        <?= Html::a('← Back to Group', ['view-group', 'id' => $model->id], ['class' => 'btn-back']) ?>
    </div>

    <div class="form-card">
        <?php $form = \yii\widgets\ActiveForm::begin(); ?>

        <div class="form-group">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'class' => 'form-control'])->label('Group Title', ['class' => 'form-label']) ?>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <?= Html::label('Start Date', 'workplangroup-start_date', ['class' => 'form-label']) ?>
                    <?= DatePicker::widget([
                        'model' => $model,
                        'attribute' => 'start_date',
                        'options' => ['placeholder' => 'Select start date...', 'class' => 'form-control'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ]) ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <?= Html::label('End Date', 'workplangroup-end_date', ['class' => 'form-label']) ?>
                    <?= DatePicker::widget([
                        'model' => $model,
                        'attribute' => 'end_date',
                        'options' => ['placeholder' => 'Select end date...', 'class' => 'form-control'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'description')->textarea(['rows' => 4, 'class' => 'form-control', 'placeholder' => 'Optional description...'])->label('Description (Optional)', ['class' => 'form-label']) ?>
        </div>

        <div class="form-group" style="margin-top: 30px;">
            <?= Html::submitButton('💾 Update Group', ['class' => 'btn-submit']) ?>
        </div>

        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>
</div>
