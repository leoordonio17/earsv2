<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\TaskType;
use common\models\TaskCategory;
use common\models\ProjectStage;
use common\models\Workplan;

/* @var $this yii\web\View */
/* @var $model common\models\Workplan */
/* @var $form yii\widgets\ActiveForm */

// Get user projects
$userProjects = Workplan::getUserProjects(Yii::$app->user->id);

// Get templates
$templates = Workplan::getTemplates(Yii::$app->user->id);

// Get task types
$taskTypes = ArrayHelper::map(
    TaskType::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
    'id',
    'name'
);

// Get task categories (will be updated via AJAX)
$taskCategories = [];
if ($model->task_type_id) {
    $taskCategories = Workplan::getTaskCategoriesByType($model->task_type_id);
}

// Get project stages
$projectStages = ArrayHelper::map(
    ProjectStage::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
    'id',
    'name'
);
?>

<style>
    .form-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        max-width: 900px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #2D1F13;
        font-weight: 600;
        font-size: 14px;
    }

    .form-label .required {
        color: #c92a2a;
        margin-left: 3px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 15px;
        color: #2D1F13;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #967259;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .help-block {
        margin-top: 6px;
        color: #c92a2a;
        font-size: 13px;
    }

    .btn-submit {
        padding: 14px 32px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
        margin-top: 10px;
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(150, 114, 89, 0.4);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #f8f5f1;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .checkbox-group label {
        margin: 0;
        cursor: pointer;
        color: #2D1F13;
        font-weight: 500;
    }

    .template-section {
        background: #f8f5f1;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border: 2px solid #D4A574;
    }

    .template-controls {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .template-controls .form-group {
        flex: 1;
        margin-bottom: 0;
    }

    .btn-load-template {
        padding: 12px 24px;
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        height: 46px;
    }

    .btn-load-template:hover {
        background: linear-gradient(135deg, #D4A574 0%, #E8D4BC 100%);
        transform: translateY(-2px);
    }

    .btn-load-template:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .template-name-field {
        display: none;
    }

    .template-name-field.show {
        display: block;
    }

    .select2-container--krajee .select2-selection {
        border: 2px solid #D4A574 !important;
        border-radius: 8px !important;
        min-height: 46px !important;
    }

    .select2-container--krajee .select2-selection:focus {
        border-color: #967259 !important;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .template-controls {
            flex-direction: column;
        }

        .btn-load-template {
            width: 100%;
        }
    }
</style>

<div class="form-card">
    <div style="background: #f8f5f1; border-left: 4px solid #967259; padding: 15px; margin-bottom: 25px; border-radius: 8px;">
        <p style="margin: 0; color: #2D1F13; font-size: 14px;">
            <strong>📝 Form Instructions:</strong> Fields marked with <span style="color: #c92a2a; font-weight: bold;">*</span> are required. 
            End date must not be earlier than start date. Template name is only required if you check "Save as Template".
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'workplan-form',
        'options' => ['class' => 'workplan-form'],
    ]); ?>

    <?php if (!empty($templates)): ?>
    <div class="template-section">
        <h3 style="margin-top: 0; color: #2D1F13; font-size: 16px; margin-bottom: 15px;">
            📋 Load from Template
        </h3>
        <div class="template-controls">
            <?= Html::dropDownList('template_id', null, $templates, [
                'class' => 'form-control',
                'id' => 'template-select',
                'prompt' => '-- Select a Template --',
            ]) ?>
            <button type="button" class="btn-load-template" id="load-template-btn" disabled>
                Load Template
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <?= $form->field($model, 'project_id')->widget(Select2::class, [
            'data' => $userProjects,
            'options' => [
                'placeholder' => 'Select a project...',
                'id' => 'project-select',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Project <span class="required">*</span>')->hint('Required field. Select from your assigned projects', ['style' => 'color: #666; font-size: 13px;']) ?>
        <?= $form->field($model, 'project_name')->hiddenInput(['id' => 'workplan-project_name'])->label(false) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'task_type_id')->widget(Select2::class, [
            'data' => $taskTypes,
            'options' => [
                'placeholder' => 'Select task type...',
                'id' => 'task-type-select',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Task Type <span class="required">*</span>')->hint('Required field. Category options will update based on task type', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'task_category_id')->widget(Select2::class, [
            'data' => $taskCategories,
            'options' => [
                'placeholder' => 'Select task category...',
                'id' => 'task-category-select',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Task Category <span class="required">*</span>')->hint('Required field. Select task type first to see categories', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'project_stage_id')->widget(Select2::class, [
            'data' => $projectStages,
            'options' => [
                'placeholder' => 'Select project stage...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Project Stage <span class="required">*</span>')->hint('Required field', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="form-row">
        <div class="form-group">
            <?= $form->field($model, 'start_date')->widget(DatePicker::class, [
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'class' => 'form-control',
                ],
            ])->label('Start Date <span class="required">*</span>')->hint('Required field', ['style' => 'color: #666; font-size: 13px;']) ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'end_date')->widget(DatePicker::class, [
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ],
                'options' => [
                    'class' => 'form-control',
                ],
            ])->label('End Date <span class="required">*</span>')->hint('Required. Must not be earlier than start date', ['style' => 'color: #666; font-size: 13px;']) ?>
        </div>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'workplan')->textarea([
            'rows' => 6,
            'placeholder' => 'Describe the workplan in detail...',
        ])->label('Workplan Description <span class="required">*</span>')->hint('Required field. Provide detailed description of the workplan', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="checkbox-group">
        <?= $form->field($model, 'is_template')->checkbox([
            'id' => 'is-template-checkbox',
            'label' => 'Save as Template',
        ])->label(false) ?>
    </div>

    <div class="form-group template-name-field" id="template-name-field">
        <?= $form->field($model, 'template_name')->textInput([
            'maxlength' => true,
            'placeholder' => 'Enter a name for this template...',
        ])->label('Template Name <span class="required">*</span>')->hint('Required when saving as template', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('💾 ' . ($model->isNewRecord ? 'Create Workplan' : 'Update Workplan'), [
            'class' => 'btn-submit'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$loadTemplateUrl = \yii\helpers\Url::to(['workplan/load-template']);
$getCategoriesUrl = \yii\helpers\Url::to(['workplan/get-categories']);

$this->registerJs(<<<JS
    // Template selection
    $('#template-select').on('change', function() {
        var templateId = $(this).val();
        $('#load-template-btn').prop('disabled', !templateId);
    });

    // Load template
    $('#load-template-btn').on('click', function() {
        var templateId = $('#template-select').val();
        if (!templateId) return;

        $.ajax({
            url: '{$loadTemplateUrl}',
            data: { id: templateId },
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // Update project (for Select2, need to create option if doesn't exist)
                    var projectSelect = $('#project-select');
                    if (projectSelect.find("option[value='" + data.project_id + "']").length === 0) {
                        projectSelect.append(new Option(data.project_name, data.project_id, false, true));
                    } else {
                        projectSelect.val(data.project_id);
                    }
                    projectSelect.trigger('change');

                    // Update other fields
                    $('#task-type-select').val(data.task_type_id).trigger('change');
                    
                    // Wait a bit for categories to load, then select
                    setTimeout(function() {
                        $('#task-category-select').val(data.task_category_id).trigger('change');
                    }, 500);
                    
                    $('#workplan-project_stage_id').val(data.project_stage_id).trigger('change');
                    $('#workplan-workplan').val(data.workplan);
                    
                    alert('Template loaded successfully!');
                } else {
                    alert('Failed to load template: ' + (response.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Error loading template. Please try again.');
            }
        });
    });

    // Task type change - load categories
    $('#task-type-select').on('change', function() {
        var taskTypeId = $(this).val();
        var categorySelect = $('#task-category-select');
        
        // Clear current categories
        categorySelect.empty().append('<option value="">Select task category...</option>').trigger('change');
        
        if (taskTypeId) {
            $.ajax({
                url: '{$getCategoriesUrl}',
                data: { task_type_id: taskTypeId },
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.categories) {
                        $.each(response.categories, function(index, category) {
                            var option = new Option(category.name, category.id, false, false);
                            categorySelect.append(option);
                        });
                        categorySelect.trigger('change');
                    }
                }
            });
        }
    });

    // Project select change - update project_name hidden field
    $('#project-select').on('change', function() {
        var selectedText = $(this).find('option:selected').text();
        $('#workplan-project_name').val(selectedText);
    });

    // Save as template checkbox
    $('#is-template-checkbox').on('change', function() {
        if ($(this).is(':checked')) {
            $('#template-name-field').addClass('show');
        } else {
            $('#template-name-field').removeClass('show');
        }
    });

    // Initialize template name field visibility
    if ($('#is-template-checkbox').is(':checked')) {
        $('#template-name-field').addClass('show');
    }

    // Initialize project_name on page load if project is selected
    $(document).ready(function() {
        var projectSelect = $('#project-select');
        if (projectSelect.val()) {
            var selectedText = projectSelect.find('option:selected').text();
            $('#workplan-project_name').val(selectedText);
        }
    });
JS
);
?>
