<?php

use yii\helpers\Html;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\TaskType;
use common\models\TaskCategory;
use common\models\ProjectStage;
use common\models\Workplan;

/* @var $this yii\web\View */
/* @var $submittedData array */

// Get user projects
$userProjects = Workplan::getUserProjects(Yii::$app->user->id);

// Get task types
$taskTypes = ArrayHelper::map(
    TaskType::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
    'id',
    'name'
);

// Get project stages
$projectStages = ArrayHelper::map(
    ProjectStage::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
    'id',
    'name'
);

$submittedDataJson = json_encode($submittedData ?? []);
?>

<style>
    .form-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .instructions {
        background: #f8f5f1;
        border-left: 4px solid #967259;
        padding: 15px;
        margin-bottom: 25px;
        border-radius: 8px;
    }

    .instructions p {
        margin: 0;
        color: #2D1F13;
        font-size: 14px;
    }

    .workplan-rows {
        margin-bottom: 20px;
    }

    .workplan-row {
        background: #f8f9fa;
        border: 2px solid #D4A574;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        position: relative;
    }

    .row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #E8D4BC;
    }

    .row-number {
        font-size: 18px;
        font-weight: 600;
        color: #2D1F13;
    }

    .btn-remove-row {
        padding: 8px 16px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-remove-row:hover {
        background: #c82333;
        transform: scale(1.05);
    }

    .form-row-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2D1F13;
        font-weight: 600;
        font-size: 14px;
    }

    .required {
        color: #c92a2a;
        margin-left: 3px;
    }

    .form-control, select {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 14px;
        color: #2D1F13;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus, select:focus {
        outline: none;
        border-color: #967259;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .select2-container--krajee .select2-selection {
        border: 2px solid #D4A574 !important;
        border-radius: 8px !important;
        min-height: 42px !important;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #E8D4BC;
    }

    .btn-add-row {
        padding: 12px 24px;
        background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .btn-add-row:hover {
        background: linear-gradient(135deg, #34ce57 0%, #5fdd7d 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
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
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(150, 114, 89, 0.4);
    }

    @media (max-width: 768px) {
        .form-row-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="form-card">
    <div class="instructions">
        <p>
            <strong>📝 Instructions:</strong> First, set the overall workplan title and date range (e.g., "Workplan for January 2026"). 
            Then add individual workplans by clicking "+ Add Another Workplan". Fields marked with <span class="required">*</span> are required.
        </p>
    </div>

    <form id="workplan-form" method="post" action="<?= \yii\helpers\Url::to(['workplan/create']) ?>">
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        
        <!-- Workplan Group Section -->
        <div class="workplan-group-section" style="background: #f0f8ff; border: 2px solid #967259; border-radius: 12px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #2D1F13; margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 2px solid #D4A574; padding-bottom: 10px;">
                📋 Workplan Group Details
            </h3>
            
            <div class="form-group">
                <label>Workplan Title <span class="required">*</span></label>
                <input type="text" name="WorkplanGroup[title]" class="form-control" 
                       placeholder="e.g., Workplan for January 2026" 
                       value="<?= isset($submittedData['group']['title']) ? Html::encode($submittedData['group']['title']) : '' ?>" 
                       required>
                <small style="color: #666; font-size: 13px; margin-top: 5px; display: block;">Give a descriptive title for this group of workplans</small>
            </div>

            <div class="form-row-grid">
                <div class="form-group">
                    <label>Group Start Date <span class="required">*</span></label>
                    <input type="date" name="WorkplanGroup[start_date]" class="form-control" 
                           value="<?= isset($submittedData['group']['start_date']) ? $submittedData['group']['start_date'] : '' ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label>Group End Date <span class="required">*</span></label>
                    <input type="date" name="WorkplanGroup[end_date]" class="form-control" 
                           value="<?= isset($submittedData['group']['end_date']) ? $submittedData['group']['end_date'] : '' ?>" 
                           required>
                    <small style="color: #666; font-size: 13px; margin-top: 5px; display: block;">Must not be earlier than start date</small>
                </div>
            </div>

            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="WorkplanGroup[description]" class="form-control" rows="3" 
                          placeholder="Optional description for this workplan group..."><?= isset($submittedData['group']['description']) ? Html::encode($submittedData['group']['description']) : '' ?></textarea>
            </div>
        </div>

        <h3 style="color: #2D1F13; margin-bottom: 20px; font-size: 18px;">
            📝 Individual Workplans
        </h3>
        
        <div id="workplan-rows" class="workplan-rows">
            <!-- Initial row will be added by JavaScript -->
        </div>

        <div class="action-buttons">
            <button type="button" class="btn-add-row" id="add-row-btn">
                ➕ Add Another Workplan
            </button>
            <button type="submit" class="btn-submit">
                💾 Save All Workplans
            </button>
        </div>
    </form>
</div>

<?php
$getCategoriesUrl = \yii\helpers\Url::to(['workplan/get-categories']);
$userProjectsJson = json_encode($userProjects);
$taskTypesJson = json_encode($taskTypes);
$projectStagesJson = json_encode($projectStages);

$this->registerJs(<<<JS
let rowCount = 0;
const userProjects = {$userProjectsJson};
const taskTypes = {$taskTypesJson};
const projectStages = {$projectStagesJson};
const submittedData = {$submittedDataJson};

function createProjectOptions() {
    let options = '<option value="">Select a project...</option>';
    for (let id in userProjects) {
        options += '<option value="' + id + '">' + userProjects[id] + '</option>';
    }
    return options;
}

function createTaskTypeOptions() {
    let options = '<option value="">Select task type...</option>';
    for (let id in taskTypes) {
        options += '<option value="' + id + '">' + taskTypes[id] + '</option>';
    }
    return options;
}

function createProjectStageOptions() {
    let options = '<option value="">Select project stage...</option>';
    for (let id in projectStages) {
        options += '<option value="' + id + '">' + projectStages[id] + '</option>';
    }
    return options;
}

function addWorkplanRow() {
    rowCount++;
    const rowHtml = `
        <div class="workplan-row" data-row="\${rowCount}">
            <div class="row-header">
                <div class="row-number">Workplan #\${rowCount}</div>
                \${rowCount > 1 ? '<button type="button" class="btn-remove-row" onclick="removeRow(\${rowCount})">🗑️ Remove</button>' : ''}
            </div>
            
            <div class="form-row-grid">
                <div class="form-group">
                    <label>Project <span class="required">*</span></label>
                    <select name="Workplan[\${rowCount}][project_id]" class="form-control project-select" data-row="\${rowCount}" required>
                        \${createProjectOptions()}
                    </select>
                    <input type="hidden" name="Workplan[\${rowCount}][project_name]" class="project-name-hidden" data-row="\${rowCount}">
                </div>

                <div class="form-group">
                    <label>Task Type <span class="required">*</span></label>
                    <select name="Workplan[\${rowCount}][task_type_id]" class="form-control task-type-select" data-row="\${rowCount}" required>
                        \${createTaskTypeOptions()}
                    </select>
                </div>
            </div>

            <div class="form-row-grid">
                <div class="form-group">
                    <label>Task Category <span class="required">*</span></label>
                    <select name="Workplan[\${rowCount}][task_category_id]" class="form-control task-category-select" data-row="\${rowCount}" required>
                        <option value="">Select task type first...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Project Stage <span class="required">*</span></label>
                    <select name="Workplan[\${rowCount}][project_stage_id]" class="form-control" required>
                        \${createProjectStageOptions()}
                    </select>
                </div>
            </div>

            <div class="form-row-grid">
                <div class="form-group">
                    <label>Start Date <span class="required">*</span></label>
                    <input type="date" name="Workplan[\${rowCount}][start_date]" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>End Date <span class="required">*</span></label>
                    <input type="date" name="Workplan[\${rowCount}][end_date]" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Workplan Description <span class="required">*</span></label>
                <textarea name="Workplan[\${rowCount}][workplan]" class="form-control" rows="4" placeholder="Describe the workplan in detail..." required></textarea>
            </div>
        </div>
    `;
    
    $('#workplan-rows').append(rowHtml);
    attachEventHandlers(rowCount);
}

function removeRow(rowNum) {
    $('.workplan-row[data-row="' + rowNum + '"]').remove();
}

function attachEventHandlers(rowNum) {
    // Project select change
    $('.project-select[data-row="' + rowNum + '"]').on('change', function() {
        const selectedValue = $(this).val();
        const selectedText = $(this).find('option:selected').text();
        
        if (selectedValue === 'NOT_PROJECT_RELATED') {
            $('.project-name-hidden[data-row="' + rowNum + '"]').val('Not Project Related');
        } else {
            $('.project-name-hidden[data-row="' + rowNum + '"]').val(selectedText);
        }
    });

    // Task type change
    $('.task-type-select[data-row="' + rowNum + '"]').on('change', function() {
        const taskTypeId = $(this).val();
        const categorySelect = $('.task-category-select[data-row="' + rowNum + '"]');
        
        categorySelect.empty().append('<option value="">Loading...</option>');
        
        if (taskTypeId) {
            $.ajax({
                url: '{$getCategoriesUrl}',
                data: { task_type_id: taskTypeId },
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    categorySelect.empty().append('<option value="">Select task category...</option>');
                    if (response.success && response.categories) {
                        $.each(response.categories, function(index, category) {
                            categorySelect.append('<option value="' + category.id + '">' + category.name + '</option>');
                        });
                    }
                }
            });
        } else {
            categorySelect.empty().append('<option value="">Select task type first...</option>');
        }
    });
}

// Add first row on load
$(document).ready(function() {
    // If there's submitted data, restore it
    if (submittedData.workplans && Object.keys(submittedData.workplans).length > 0) {
        for (let key in submittedData.workplans) {
            addWorkplanRow();
            const rowData = submittedData.workplans[key];
            const currentRow = rowCount;
            
            // Populate fields
            setTimeout(function() {
                $('select[name="Workplan[' + currentRow + '][project_id]"]').val(rowData.project_id).trigger('change');
                $('select[name="Workplan[' + currentRow + '][task_type_id]"]').val(rowData.task_type_id).trigger('change');
                
                // Wait for categories to load, then set
                setTimeout(function() {
                    $('select[name="Workplan[' + currentRow + '][task_category_id]"]').val(rowData.task_category_id);
                }, 600);
                
                $('select[name="Workplan[' + currentRow + '][project_stage_id]"]').val(rowData.project_stage_id);
                $('input[name="Workplan[' + currentRow + '][start_date]"]').val(rowData.start_date);
                $('input[name="Workplan[' + currentRow + '][end_date]"]').val(rowData.end_date);
                $('textarea[name="Workplan[' + currentRow + '][workplan]"]').val(rowData.workplan);
            }, 100);
        }
    } else {
        // No submitted data, add one empty row
        addWorkplanRow();
    }
});

// Add row button
$('#add-row-btn').on('click', function() {
    addWorkplanRow();
});

// Form validation
$('#workplan-form').on('submit', function(e) {
    const rows = $('.workplan-row').length;
    if (rows === 0) {
        e.preventDefault();
        alert('Please add at least one workplan.');
        return false;
    }
    return true;
});

JS
);
?>
