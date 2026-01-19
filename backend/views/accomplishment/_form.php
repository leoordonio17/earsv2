<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\Accomplishment;
use common\models\Status;
use common\models\Milestone;

/* @var $this yii\web\View */
/* @var $model common\models\Accomplishment */
/* @var $form yii\widgets\ActiveForm */

$userId = Yii::$app->user->id;

// Get user's workplans
$workplans = Accomplishment::getUserWorkplans($userId);

// Check if workplan is pre-selected
$isWorkplanPreSelected = !$model->isNewRecord || $model->workplan_id;
$selectedWorkplan = null;
if ($isWorkplanPreSelected && $model->workplan_id) {
    $selectedWorkplan = \common\models\Workplan::findOne($model->workplan_id);
}

// Get statuses
$statuses = ArrayHelper::map(
    Status::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
    'id',
    'name'
);

// Get milestones
$milestones = ArrayHelper::map(
    Milestone::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
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

    .checkbox-section {
        background: #f8f5f1;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border: 2px solid #D4A574;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 0;
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
        font-weight: 600;
        font-size: 15px;
    }

    .conditional-fields {
        display: none;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #D4A574;
    }

    .conditional-fields.show {
        display: block;
    }

    .radio-group {
        display: flex;
        gap: 20px;
        padding: 12px;
        background: white;
        border-radius: 8px;
        border: 2px solid #D4A574;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .radio-option input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .radio-option label {
        margin: 0;
        cursor: pointer;
        color: #2D1F13;
        font-weight: 500;
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

    .delay-reason-field {
        display: none;
    }

    .delay-reason-field.show {
        display: block;
    }

    .workplan-details {
        background: #e8f4f8;
        border-left: 4px solid #1976d2;
        padding: 20px;
        margin-bottom: 25px;
        border-radius: 8px;
    }

    .workplan-details h4 {
        margin: 0 0 15px 0;
        color: #1976d2;
        font-size: 16px;
        font-weight: 600;
    }

    .workplan-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .workplan-info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .workplan-info-label {
        font-weight: 600;
        color: #2D1F13;
        font-size: 13px;
    }

    .workplan-info-value {
        color: #555;
        font-size: 14px;
    }

    .workplan-description {
        padding: 12px;
        background: white;
        border-radius: 6px;
        color: #2D1F13;
        line-height: 1.6;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .radio-group {
            flex-direction: column;
        }
    }
</style>

<div class="form-card">
    <div style="background: #f8f5f1; border-left: 4px solid #967259; padding: 15px; margin-bottom: 25px; border-radius: 8px;">
        <p style="margin: 0; color: #2D1F13; font-size: 14px;">
            <strong>📝 Form Instructions:</strong> Fields marked with <span style="color: #c92a2a; font-weight: bold;">*</span> are required. 
            End date must not be earlier than start date. Additional fields become required when you check "Final Task for Deliverable/Milestone".
        </p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'accomplishment-form',
        'options' => ['class' => 'accomplishment-form'],
    ]); ?>

    <?php if ($selectedWorkplan): ?>
        <div class="workplan-details">
            <h4>📋 Selected Workplan</h4>
            <div class="workplan-info-grid">
                <div class="workplan-info-item">
                    <span class="workplan-info-label">Project</span>
                    <span class="workplan-info-value"><?= Html::encode($selectedWorkplan->project_name) ?></span>
                </div>
                <div class="workplan-info-item">
                    <span class="workplan-info-label">Task Type</span>
                    <span class="workplan-info-value"><?= Html::encode($selectedWorkplan->taskType->name ?? 'N/A') ?></span>
                </div>
                <div class="workplan-info-item">
                    <span class="workplan-info-label">Start Date</span>
                    <span class="workplan-info-value"><?= Yii::$app->formatter->asDate($selectedWorkplan->start_date, 'php:M d, Y') ?></span>
                </div>
                <div class="workplan-info-item">
                    <span class="workplan-info-label">End Date</span>
                    <span class="workplan-info-value"><?= Yii::$app->formatter->asDate($selectedWorkplan->end_date, 'php:M d, Y') ?></span>
                </div>
            </div>
            <div class="workplan-info-item">
                <span class="workplan-info-label">Workplan Description</span>
                <div class="workplan-description">
                    <?= nl2br(Html::encode($selectedWorkplan->workplan)) ?>
                </div>
            </div>
        </div>
        <?= Html::activeHiddenInput($model, 'workplan_id') ?>
    <?php else: ?>
        <div class="form-group">
            <?= $form->field($model, 'workplan_id')->widget(Select2::class, [
                'data' => $workplans,
                'options' => [
                    'placeholder' => 'Select a workplan...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('Workplan <span class="required">*</span>')->hint('Required field. Select the workplan this accomplishment is related to', ['style' => 'color: #666; font-size: 13px;']) ?>
        </div>
    <?php endif; ?>

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
        <?= $form->field($model, 'accomplished_task')->textarea([
            'rows' => 6,
            'placeholder' => 'Describe the accomplished task or activity in detail...',
        ])->label('Accomplished Task/Activity <span class="required">*</span>')->hint('Required field. Provide detailed description of what was accomplished', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'status_id')->widget(Select2::class, [
            'data' => $statuses,
            'options' => [
                'placeholder' => 'Select status...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Status <span class="required">*</span>')->hint('Required field', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="form-group">
        <?= $form->field($model, 'mode_of_delivery')->widget(Select2::class, [
            'data' => [
                'Onsite' => '🏢 Onsite',
                'WFH' => '🏠 Work From Home',
                'Hybrid' => '🔄 Hybrid',
            ],
            'options' => [
                'placeholder' => 'Select mode of delivery...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Mode of Delivery')->hint('Select how this work was delivered', ['style' => 'color: #666; font-size: 13px;']) ?>
    </div>

    <div class="checkbox-section">
        <div class="checkbox-group">
            <?= $form->field($model, 'is_final_task')->checkbox([
                'id' => 'is-final-task-checkbox',
                'label' => 'Final Task for Deliverable/Milestone?',
            ])->label(false)->hint('Check this if this accomplishment represents the final task for a milestone or deliverable', ['style' => 'color: #666; font-size: 13px; margin-top: 8px;']) ?>
        </div>

        <div class="conditional-fields" id="final-task-fields">
            <div class="form-group">
                <?= $form->field($model, 'milestone_id')->widget(Select2::class, [
                    'data' => $milestones,
                    'options' => [
                        'placeholder' => 'Select milestone...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Milestone <span class="required">*</span>')->hint('Required when this is a final task', ['style' => 'color: #666; font-size: 13px;']) ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <?= $form->field($model, 'target_deadline')->widget(DatePicker::class, [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                        'options' => [
                            'class' => 'form-control',
                        ],
                    ])->label('Target Deadline for Milestone <span class="required">*</span>')->hint('Required when this is a final task', ['style' => 'color: #666; font-size: 13px;']) ?>
                </div>

                <div class="form-group">
                    <?= $form->field($model, 'actual_submission_date')->widget(DatePicker::class, [
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                        'options' => [
                            'class' => 'form-control',
                        ],
                    ])->label('Actual Date of Submission <span class="required">*</span>')->hint('Required when this is a final task', ['style' => 'color: #666; font-size: 13px;']) ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::label('Within Target? <span class="required">*</span>', 'accomplishment-within_target', ['class' => 'form-label']) ?>
                <small style="display: block; color: #666; font-size: 13px; margin-bottom: 8px;">Required when this is a final task. Indicate if submission was within the target deadline</small>
                <div class="radio-group">
                    <div class="radio-option">
                        <?= Html::radio('Accomplishment[within_target]', $model->within_target === true, [
                            'value' => 1,
                            'id' => 'accomplishment-within_target-1',
                        ]) ?>
                        <?= Html::label('Yes', 'accomplishment-within_target-1') ?>
                    </div>
                    <div class="radio-option">
                        <?= Html::radio('Accomplishment[within_target]', $model->within_target === false, [
                            'value' => 0,
                            'id' => 'accomplishment-within_target-0',
                        ]) ?>
                        <?= Html::label('No', 'accomplishment-within_target-0') ?>
                    </div>
                </div>
            </div>

            <div class="form-group delay-reason-field" id="delay-reason-field">
                <?= $form->field($model, 'reason_for_delay')->textarea([
                    'rows' => 4,
                    'placeholder' => 'Explain the reason for the delay...',
                ])->label('Reason for Delay <span class="required">*</span>')->hint('Required when submission is not within target deadline', ['style' => 'color: #666; font-size: 13px;']) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('💾 ' . ($model->isNewRecord ? 'Create Accomplishment' : 'Update Accomplishment'), [
            'class' => 'btn-submit'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJs(<<<JS
    // Toggle final task fields
    function toggleFinalTaskFields() {
        if ($('#is-final-task-checkbox').is(':checked')) {
            $('#final-task-fields').addClass('show');
        } else {
            $('#final-task-fields').removeClass('show');
        }
    }

    // Toggle delay reason field
    function toggleDelayReasonField() {
        if ($('#is-final-task-checkbox').is(':checked') && $('#accomplishment-within_target-0').is(':checked')) {
            $('#delay-reason-field').addClass('show');
        } else {
            $('#delay-reason-field').removeClass('show');
        }
    }

    // Event listeners
    $('#is-final-task-checkbox').on('change', function() {
        toggleFinalTaskFields();
        toggleDelayReasonField();
    });

    $('input[name="Accomplishment[within_target]"]').on('change', function() {
        toggleDelayReasonField();
    });

    // Initialize on page load
    toggleFinalTaskFields();
    toggleDelayReasonField();
JS
);
?>
