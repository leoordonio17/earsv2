<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Workplan */
/* @var $form yii\widgets\ActiveForm */

$taskTypes = \common\models\TaskType::find()->select(['name', 'id'])->where(['is_active' => 1])->indexBy('id')->column();
$projectStages = \common\models\ProjectStage::find()->select(['name', 'id'])->where(['is_active' => 1])->indexBy('id')->column();
$userProjects = \common\models\Workplan::getUserProjects(Yii::$app->user->id);
?>

<style>
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 8px;
        display: block;
    }

    .form-control, .select2-container {
        border: 2px solid #D4A574;
        border-radius: 8px;
    }

    .form-control:focus {
        border-color: #967259;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    textarea.form-control {
        min-height: 100px;
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

<?php $form = \yii\widgets\ActiveForm::begin([
    'id' => 'workplan-form',
    'options' => ['class' => 'workplan-form'],
]); ?>

<div class="form-group">
    <?= Html::label('Project', 'workplan-project_id', ['class' => 'form-label']) ?>
    <?= Select2::widget([
        'model' => $model,
        'attribute' => 'project_id',
        'data' => $userProjects,
        'options' => [
            'placeholder' => 'Select a project...',
            'id' => 'workplan-project_id',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
        'pluginEvents' => [
            'select2:select' => 'function(e) {
                var selectedText = e.params.data.text;
                $("#workplan-project_name").val(selectedText);
            }',
        ],
    ]) ?>
    <?= Html::activeHiddenInput($model, 'project_name', ['id' => 'workplan-project_name']) ?>
</div>

<div class="form-group">
    <?= Html::label('Task Type', 'workplan-task_type_id', ['class' => 'form-label']) ?>
    <?= Select2::widget([
        'model' => $model,
        'attribute' => 'task_type_id',
        'data' => $taskTypes,
        'options' => [
            'placeholder' => 'Select task type...',
            'id' => 'workplan-task_type_id',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
        'pluginEvents' => [
            'select2:select' => 'function(e) {
                var taskTypeId = e.params.data.id;
                loadTaskCategories(taskTypeId);
            }',
        ],
    ]) ?>
</div>

<div class="form-group">
    <?= Html::label('Task Category', 'workplan-task_category_id', ['class' => 'form-label']) ?>
    <?= Select2::widget([
        'model' => $model,
        'attribute' => 'task_category_id',
        'data' => $model->task_type_id ? \common\models\Workplan::getTaskCategoriesByType($model->task_type_id) : [],
        'options' => [
            'placeholder' => 'Select task category...',
            'id' => 'workplan-task_category_id',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ]) ?>
</div>

<div class="form-group">
    <?= Html::label('Project Stage', 'workplan-project_stage_id', ['class' => 'form-label']) ?>
    <?= Select2::widget([
        'model' => $model,
        'attribute' => 'project_stage_id',
        'data' => $projectStages,
        'options' => [
            'placeholder' => 'Select project stage...',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ]) ?>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <?= Html::label('Start Date', 'workplan-start_date', ['class' => 'form-label']) ?>
            <?= DatePicker::widget([
                'model' => $model,
                'attribute' => 'start_date',
                'options' => ['placeholder' => 'Select start date...'],
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
            <?= Html::label('End Date', 'workplan-end_date', ['class' => 'form-label']) ?>
            <?= DatePicker::widget([
                'model' => $model,
                'attribute' => 'end_date',
                'options' => ['placeholder' => 'Select end date...'],
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
    <?= $form->field($model, 'workplan')->textarea(['rows' => 6, 'placeholder' => 'Enter workplan description...'])->label('Workplan Description', ['class' => 'form-label']) ?>
</div>

<div class="form-group" style="margin-top: 30px;">
    <?= Html::submitButton('💾 Save Workplan', ['class' => 'btn-submit']) ?>
</div>

<?php \yii\widgets\ActiveForm::end(); ?>

<script>
function loadTaskCategories(taskTypeId) {
    if (!taskTypeId) {
        $('#workplan-task_category_id').empty().trigger('change');
        return;
    }

    $.ajax({
        url: '<?= \yii\helpers\Url::to(['workplan/get-categories']) ?>',
        type: 'GET',
        data: {
            task_type_id: taskTypeId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var $select = $('#workplan-task_category_id');
                $select.empty();
                $select.append('<option value="">Select task category...</option>');
                
                $.each(response.categories, function(index, category) {
                    $select.append('<option value="' + category.id + '">' + category.name + '</option>');
                });
                
                $select.trigger('change');
            }
        },
        error: function() {
            alert('Failed to load task categories');
        }
    });
}

// Set project name on page load if project is already selected
$(document).ready(function() {
    var projectId = $('#workplan-project_id').val();
    if (projectId) {
        var projectText = $('#workplan-project_id option:selected').text();
        $('#workplan-project_name').val(projectText);
    }
});
</script>
