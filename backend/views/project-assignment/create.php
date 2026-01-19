<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\ProjectAssignment;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\ProjectAssignment */

$this->title = 'Create Project Assignment';
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
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        color: #E8D4BC;
        text-decoration: none !important;
    }

    .btn-back:focus,
    .btn-back:active {
        text-decoration: none !important;
        outline: none;
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
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 16px;
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

    /* Customize Select2 to match brown theme */
    .select2-container--default .select2-selection--multiple {
        border: 2px solid #D4A574 !important;
        border-radius: 8px !important;
        min-height: 50px !important;
        padding: 4px 8px !important;
        font-size: 16px !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #967259 !important;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #967259 !important;
        border: 1px solid #967259 !important;
        color: white !important;
        border-radius: 5px !important;
        padding: 5px 12px !important;
        font-size: 15px !important;
        margin: 4px 6px 4px 0 !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
        margin-right: 5px !important;
        font-size: 16px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #E8D4BC !important;
    }

    .select2-container--default .select2-selection--multiple .select2-search__field {
        font-size: 16px !important;
        padding: 4px !important;
    }

    .select2-results__option {
        font-size: 15px !important;
        padding: 10px 15px !important;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: #967259 !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #D4A574 !important;
        color: white !important;
    }

    .select2-dropdown {
        border: 2px solid #D4A574 !important;
        border-radius: 8px !important;
    }

    .select2-search--dropdown .select2-search__field {
        border: 2px solid #D4A574 !important;
        border-radius: 6px !important;
        padding: 8px 12px !important;
        font-size: 15px !important;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: #967259 !important;
        outline: none !important;
    }

    /* Project dropdown - ensure full text visible */
    .form-control {
        white-space: normal !important;
        overflow: visible !important;
    }

    select.form-control {
        height: 50px !important;
        font-size: 16px !important;
        padding: 12px 16px !important;
    }

    select.form-control option {
        padding: 10px !important;
        font-size: 15px !important;
        white-space: normal !important;
        word-wrap: break-word !important;
    }

    /* Multi-select dropdown styling */
    .multi-select {
        min-height: 120px;
    }

    .help-block {
        color: #e74c3c;
        font-size: 14px;
        margin-top: 6px;
    }

    small {
        font-size: 14px;
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h1>
            <span>➕</span>
            Create New Project Assignment
        </h1>
        <?= Html::a('<span>←</span> Back to List', ['index'], ['class' => 'btn-back']) ?>
    </div>

    <div class="form-card">
        <?php $form = ActiveForm::begin([
            'id' => 'project-assignment-form',
            'options' => ['class' => 'project-assignment-form'],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'help-block'],
            ],
        ]); ?>

        <div class="form-group field-projectassignment-project_id">
            <?= Html::label('Project', 'project-dropdown', ['class' => 'form-label']) ?>
            <?php 
            $projects = ProjectAssignment::fetchProjectsFromAPI(true); // true = exclude assigned projects
            ?>
            <?= Html::dropDownList('project_dropdown', null, $projects, [
                'id' => 'project-dropdown',
                'class' => 'form-control',
                'prompt' => empty($projects) ? 'No projects available - Check API connection' : 'Select a project...',
            ]) ?>
            <?= $form->field($model, 'project_id')->hiddenInput(['id' => 'project-id-hidden'])->label(false) ?>
            <?= $form->field($model, 'project_name')->hiddenInput(['id' => 'project-name-hidden'])->label(false) ?>
            <small style="color: #6c757d; display: block; margin-top: 5px;">
                Projects loaded from: <?= strpos(Yii::$app->request->hostInfo, 'localhost') !== false ? 'localhost/pmisv2' : 'projects.pids.gov.ph' ?>
            </small>
        </div>

        <div class="form-group field-projectassignment-user_ids">
            <?= Html::label('Researchers (Select Multiple)', 'researcher-ids', ['class' => 'form-label']) ?>
            <?= Select2::widget([
                'name' => 'researcher_ids',
                'data' => ProjectAssignment::getResearcherDropdownList(),
                'options' => [
                    'placeholder' => 'Select researchers...',
                    'multiple' => true,
                    'id' => 'researcher-ids',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'tags' => false,
                    'maximumSelectionLength' => 50,
                    'closeOnSelect' => false,
                ],
                'addon' => [
                    'prepend' => [
                        'content' => '<i class="fas fa-users"></i>',
                    ],
                ],
                'theme' => Select2::THEME_DEFAULT,
                'size' => Select2::MEDIUM,
            ]); ?>
            <small style="color: #6c757d; display: block; margin-top: 5px;">
                Click to select multiple researchers for this project
            </small>
            <div id="researcher-ids-error" class="help-block" style="display: none; color: #e74c3c;"></div>
        </div>

        <div class="form-actions">
            <?= Html::resetButton('Reset Form', ['class' => 'btn-reset']) ?>
            <?= Html::submitButton('Create Project Assignment', ['class' => 'btn-submit']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
// Update hidden fields when project is selected
document.getElementById('project-dropdown').addEventListener('change', function(e) {
    const selectedOption = e.target.options[e.target.selectedIndex];
    const projectId = selectedOption.value;
    const projectName = selectedOption.text;
    
    document.getElementById('project-id-hidden').value = projectId;
    document.getElementById('project-name-hidden').value = projectName !== 'Select a project...' ? projectName : '';
});

// Form submission validation for multiple researchers
document.getElementById('project-assignment-form').addEventListener('submit', function(e) {
    const researcherSelect = document.getElementById('researcher-ids');
    const selectedResearchers = Array.from(researcherSelect.selectedOptions);
    const errorDiv = document.getElementById('researcher-ids-error');
    
    if (selectedResearchers.length === 0) {
        e.preventDefault();
        errorDiv.textContent = 'Please select at least one researcher.';
        errorDiv.style.display = 'block';
        researcherSelect.style.borderColor = '#e74c3c';
        researcherSelect.focus();
        return false;
    }
    
    errorDiv.style.display = 'none';
    researcherSelect.style.borderColor = '#D4A574';
    return true;
});

// Clear error on selection
document.getElementById('researcher-ids').addEventListener('change', function() {
    const errorDiv = document.getElementById('researcher-ids-error');
    if (this.selectedOptions.length > 0) {
        errorDiv.style.display = 'none';
        this.style.borderColor = '#D4A574';
    }
});
</script>
