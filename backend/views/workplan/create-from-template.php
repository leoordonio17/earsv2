<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $template common\models\WorkplanGroup */
/* @var $newGroup common\models\WorkplanGroup */

$this->title = 'Create from Template: ' . $template->template_name;
?>

<style>
    .template-create-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .template-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #E8D4BC;
    }

    .template-header h1 {
        color: #2D1F13;
        font-size: 26px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .template-info {
        background: #f8f5f1;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        border-left: 4px solid #967259;
    }

    .template-info h3 {
        color: #967259;
        margin: 0 0 15px 0;
        font-size: 18px;
    }

    .template-workplans {
        background: white;
        border: 1px solid #E8D4BC;
        border-radius: 8px;
        padding: 15px;
    }

    .workplan-item {
        padding: 10px;
        background: #f8f5f1;
        border-radius: 5px;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #E8D4BC;
        border-radius: 8px;
        font-size: 15px;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #967259;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
    }

    .btn {
        padding: 12px 28px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(150, 114, 89, 0.4);
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #666;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }
</style>

<div class="template-create-container">
    <div class="template-header">
        <h1>📚 Create Workplan from Template</h1>
        <p style="color: #666;">Fill in the details for your new workplan group based on this template</p>
    </div>

    <div class="template-info">
        <h3>📋 Template: <?= Html::encode($template->template_name) ?></h3>
        <p style="margin: 10px 0 0 0; color: #666;">
            <strong>Original Dates:</strong> 
            <?= Yii::$app->formatter->asDate($template->start_date, 'php:M d, Y') ?> 
            - <?= Yii::$app->formatter->asDate($template->end_date, 'php:M d, Y') ?>
        </p>
        
        <?php if ($template->description): ?>
            <p style="margin: 10px 0 0 0; color: #666;">
                <strong>Description:</strong> <?= Html::encode($template->description) ?>
            </p>
        <?php endif; ?>

        <div class="template-workplans" style="margin-top: 15px;">
            <strong style="color: #2D1F13;">Workplans to be copied (<?= count($template->workplans) ?>):</strong>
            <div style="margin-top: 10px;">
                <?php foreach ($template->workplans as $workplan): ?>
                    <div class="workplan-item">
                        <?= Html::encode($workplan->workplan) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php $form = ActiveForm::begin(['id' => 'create-from-template-form']); ?>

    <div class="form-group">
        <?= $form->field($newGroup, 'title')->textInput([
            'placeholder' => 'Enter workplan group title (e.g., "February 2026 Workplan")',
            'autofocus' => true
        ])->label('Workplan Group Title <span style="color:red">*</span>') ?>
    </div>

    <div class="form-group">
        <?= $form->field($newGroup, 'start_date')->input('date')->label('Start Date <span style="color:red">*</span>') ?>
    </div>

    <div class="form-group">
        <?= $form->field($newGroup, 'end_date')->input('date')->label('End Date <span style="color:red">*</span>') ?>
    </div>

    <div class="form-group">
        <?= $form->field($newGroup, 'description')->textarea([
            'rows' => 3,
            'placeholder' => 'Optional description for this workplan group'
        ])->label('Description') ?>
    </div>

    <div class="form-actions">
        <?= Html::a('Cancel', ['index', 'view' => 'templates'], ['class' => 'btn btn-secondary']) ?>
        <?= Html::submitButton('✅ Create Workplan Group', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
