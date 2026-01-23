<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var common\models\ProgressReport $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $assignedProjects */
?>

<style>
    .form-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: #967259;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E8D4BC;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        display: block;
    }

    .hint-block {
        color: #718096;
        font-size: 13px;
        margin-top: 5px;
    }

    .info-box {
        background: #f8f5f1;
        border-left: 4px solid #967259;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }

    .info-box-title {
        font-weight: 600;
        color: #967259;
        margin-bottom: 10px;
    }

    .info-box-content {
        color: #2d3748;
        font-size: 14px;
        line-height: 1.6;
    }

    .deliverables-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .deliverables-table th {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }

    .deliverables-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #2d3748;
        font-size: 14px;
    }

    .deliverables-table tr:hover {
        background: #f7fafc;
    }

    .extension-fields {
        background: #fff5f5;
        border: 2px solid #feb2b2;
        border-radius: 10px;
        padding: 20px;
        margin-top: 15px;
    }

    .file-upload-list {
        margin-top: 15px;
    }

    .file-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background: #f7fafc;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .file-item a {
        color: #967259;
        text-decoration: none;
        font-weight: 500;
    }

    .file-item a:hover {
        text-decoration: underline;
    }

    .btn-delete-file {
        background: #fc8181;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-delete-file:hover {
        background: #f56565;
    }
</style>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<div class="form-section">
    <div class="section-title">
        📅 Report Information
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'report_date')->widget(DatePicker::class, [
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'startView' => 'months',
                    'minViewMode' => 'months',
                    'todayHighlight' => true,
                ],
                'options' => ['placeholder' => 'Select month and year...'],
            ])->hint('Select the month and year for this progress report') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'status')->widget(Select2::class, [
                'data' => \common\models\ProgressReport::getStatusOptions(),
                'options' => ['placeholder' => 'Select status...'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->hint('Current status of the project') ?>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-title">
        🎯 Project & Milestone Details
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'project_id')->widget(Select2::class, [
                'data' => $assignedProjects,
                'options' => [
                    'placeholder' => 'Select project name...',
                    'id' => 'project-dropdown',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'pluginEvents' => [
                    'change' => 'function() { 
                        var projectId = $(this).val();
                        var projectName = $(this).select2("data")[0] ? $(this).select2("data")[0].text : "";
                        if (projectId && projectName) {
                            $("#progressreport-project_name").val(projectName);
                        }
                    }',
                ],
            ])->label('Project Name')->hint('Select a project assigned to you') ?>
            <?= Html::activeHiddenInput($model, 'project_name') ?>
        </div>
    </div>

    <!-- Project Information Display Area -->
    <div id="project-info-container" style="display: none; margin-top: 20px;">
        <div class="info-box" style="background: linear-gradient(135deg, #f7f3f0 0%, #faf8f6 100%); border-left: 4px solid #967259;">
            <div class="info-box-title" style="color: #967259; font-weight: 600; margin-bottom: 15px; font-size: 16px;">
                📋 Project Information
            </div>
            <div id="project-info-content" class="info-box-content" style="line-height: 1.8;">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        
        <div class="info-box" style="background: linear-gradient(135deg, #f0f7f4 0%, #f6faf8 100%); border-left: 4px solid #68a57d; margin-top: 15px;">
            <div class="info-box-title" style="color: #68a57d; font-weight: 600; margin-bottom: 15px; font-size: 16px;">
                👥 Investigators
            </div>
            <div id="investigators-content" class="info-box-content" style="line-height: 1.8;">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <?php if ($model->project_data): ?>
        <div class="info-box">
            <div class="info-box-title">📋 Saved Project Information</div>
            <div class="info-box-content">
                <?php 
                $projectData = $model->getProjectDataArray();
                foreach ($projectData as $key => $value) {
                    echo "<strong>" . Html::encode($key) . ":</strong> " . Html::encode($value) . "<br>";
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'milestone_id')->widget(Select2::class, [
                'data' => [],
                'options' => [
                    'placeholder' => 'Select milestone/deliverable...',
                    'id' => 'milestone-dropdown',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('Milestone / Deliverable')->hint('Will be populated after selecting a project') ?>
            <?= Html::activeHiddenInput($model, 'milestone_name') ?>
            <?= Html::activeHiddenInput($model, 'deliverables_data') ?>
        </div>
    </div>

    <div id="deliverables-container" class="form-group">
        <?php if ($model->deliverables_data): ?>
            <label>📦 Deliverable Details (Read Only)</label>
            <table class="deliverables-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Deliverable</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $deliverables = $model->getDeliverablesArray();
                    $index = 1;
                    foreach ($deliverables as $deliverable): 
                    ?>
                        <tr>
                            <td><?= $index++ ?></td>
                            <td><?= Html::encode($deliverable['text'] ?? $deliverable['name'] ?? 'N/A') ?></td>
                            <td><?= Html::encode($deliverable['description'] ?? '-') ?></td>
                            <td><?= Html::encode($deliverable['due_date'] ?? '-') ?></td>
                            <td><?= Html::encode($deliverable['status'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?= $form->field($model, 'remarks')->textarea([
        'rows' => 6,
        'placeholder' => 'Enter any general remarks or notes about this progress report...',
        'style' => 'resize: vertical;'
    ])->hint('Optional: Add any additional notes, observations, or comments about this progress report') ?>
</div>

<div class="form-section">
    <div class="section-title">
        ⚠️ Extension Request
    </div>

    <?= $form->field($model, 'has_extension')->widget(Select2::class, [
        'data' => [0 => 'No', 1 => 'Yes'],
        'options' => [
            'placeholder' => 'Extension required?',
            'id' => 'extension-toggle',
        ],
        'pluginOptions' => [
            'allowClear' => false,
        ],
    ])->hint('Does this project require an extension?') ?>

    <div id="extension-fields" class="extension-fields" style="<?= $model->has_extension ? 'display: block;' : 'display: none;' ?>">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'extension_date')->widget(DatePicker::class, [
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true,
                    ],
                    'options' => ['placeholder' => 'Select proposed date...'],
                ])->hint('Proposed new deadline') ?>
            </div>
        </div>

        <?= $form->field($model, 'extension_justification')->textarea([
            'rows' => 6,
            'placeholder' => 'Provide detailed justification for the extension request...'
        ])->hint('Explain why an extension is needed') ?>

        <?= $form->field($model, 'uploadedFiles[]')->fileInput([
            'multiple' => true,
            'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png',
        ])->hint('Attach supporting documents (PDF, DOC, XLS, images)') ?>

        <?php if (!$model->isNewRecord && $model->documents): ?>
            <div class="file-upload-list">
                <label>📎 Attached Documents:</label>
                <?php foreach ($model->getDocumentsArray() as $doc): ?>
                    <div class="file-item">
                        <a href="<?= Yii::$app->urlManager->baseUrl . $doc ?>" target="_blank">
                            📄 <?= basename($doc) ?>
                        </a>
                        <?= Html::a('🗑️ Delete', ['delete-document', 'id' => $model->id, 'doc' => $doc], [
                            'class' => 'btn-delete-file',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this document?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="form-section" style="text-align: center;">
    <?= Html::submitButton($model->isNewRecord ? '💾 Create Report' : '💾 Update Report', [
        'class' => 'btn btn-success',
        'style' => 'padding: 12px 40px; font-size: 16px; font-weight: 600; background: linear-gradient(135deg, #967259 0%, #B8926A 100%); border: none; border-radius: 8px;'
    ]) ?>
    <?= Html::a('← Cancel', ['index'], [
        'class' => 'btn btn-secondary',
        'style' => 'padding: 12px 40px; font-size: 16px; font-weight: 600; margin-left: 15px;'
    ]) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs("
    // Toggle extension fields
    $('#extension-toggle').on('change', function() {
        if ($(this).val() == '1') {
            $('#extension-fields').slideDown();
        } else {
            $('#extension-fields').slideUp();
        }
    });

    // Fetch deliverables when project is selected
    $('#project-dropdown').on('change', function() {
        var projectId = $(this).val();
        console.log('Project selected:', projectId);
        
        if (projectId) {
            // Clear milestone dropdown and deliverables container
            $('#milestone-dropdown').empty().val(null).trigger('change');
            $('#deliverables-container').html('<div style=\"text-align: center; padding: 20px; color: #967259;\">⏳ Loading deliverables...</div>');
            
            // Fetch deliverables from API
            $.ajax({
                url: '" . \yii\helpers\Url::to(['get-deliverables']) . "',
                type: 'GET',
                data: { projectId: projectId },
                dataType: 'json',
                success: function(response) {
                    console.log('API Response:', response);
                    
                    if (response.success && response.deliverables) {
                        console.log('Deliverables count:', response.deliverables.length);
                        
                        // Display project information
                        if (response.projectData) {
                            var projectInfo = '';
                            var projectData = response.projectData;
                            
                            if (projectData.project_name) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Project Name:</strong> <span style=\"color: #2d3748;\">' + projectData.project_name + '</span></div>';
                            }
                            if (projectData.project_description) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Description:</strong> <span style=\"color: #2d3748;\">' + projectData.project_description + '</span></div>';
                            }
                            if (projectData.project_start_date) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Start Date:</strong> <span style=\"color: #2d3748;\">' + projectData.project_start_date + '</span></div>';
                            }
                            if (projectData.project_end_date) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">End Date:</strong> <span style=\"color: #2d3748;\">' + projectData.project_end_date + '</span></div>';
                            }
                            if (projectData.project_duration) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Duration:</strong> <span style=\"color: #2d3748;\">' + projectData.project_duration + '</span></div>';
                            }
                            if (projectData.budget) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Budget:</strong> <span style=\"color: #2d3748;\">' + projectData.budget + '</span></div>';
                            }
                            if (projectData.contract_no) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Contract No:</strong> <span style=\"color: #2d3748;\">' + projectData.contract_no + '</span></div>';
                            }
                            
                            $('#project-info-content').html(projectInfo || '<em style=\"color: #a0aec0;\">No additional information available</em>');
                            
                            // Display investigators
                            var investigatorsHtml = '';
                            if (projectData.investigators && Array.isArray(projectData.investigators) && projectData.investigators.length > 0) {
                                investigatorsHtml = '<div style=\"display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;\">';
                                projectData.investigators.forEach(function(inv) {
                                    investigatorsHtml += '<div style=\"background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
                                    investigatorsHtml += '<div style=\"font-weight: 600; color: #2d3748; margin-bottom: 5px;\">' + (inv.name || 'N/A') + '</div>';
                                    if (inv.role) {
                                        investigatorsHtml += '<div style=\"font-size: 13px; color: #68a57d; margin-bottom: 3px;\">🎯 ' + inv.role + '</div>';
                                    }
                                    if (inv.institution) {
                                        investigatorsHtml += '<div style=\"font-size: 12px; color: #718096;\">🏛️ ' + inv.institution + '</div>';
                                    }
                                    investigatorsHtml += '</div>';
                                });
                                investigatorsHtml += '</div>';
                            } else {
                                investigatorsHtml = '<em style=\"color: #a0aec0;\">No investigators information available</em>';
                            }
                            $('#investigators-content').html(investigatorsHtml);
                            
                            $('#project-info-container').slideDown();
                        }
                        
                        // Populate milestone dropdown
                        var options = [new Option('Select milestone/deliverable...', '', false, false)];
                        $.each(response.deliverables, function(index, deliverable) {
                            console.log('Adding deliverable:', deliverable.text);
                            options.push(new Option(deliverable.text, deliverable.id, false, false));
                        });
                        $('#milestone-dropdown').empty().append(options).val('').trigger('change');
                        
                        // Store deliverables data for later use
                        $('#milestone-dropdown').data('deliverables', response.deliverables);
                        
                        $('#deliverables-container').html('<div style=\"padding: 10px; color: #718096;\">✓ ' + response.deliverables.length + ' deliverable(s) loaded. Select a milestone to view details.</div>');
                    } else {
                        console.error('No deliverables or failed:', response.message || 'Unknown error');
                        $('#project-info-container').slideUp();
                        $('#deliverables-container').html('<div style=\"padding: 10px; color: #e53e3e;\">⚠️ ' + (response.message || 'No deliverables found for this project.') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    $('#deliverables-container').html('<div style=\"padding: 10px; color: #e53e3e;\">⚠️ Error loading deliverables. Please check console.</div>');
                }
            });
        } else {
            $('#milestone-dropdown').empty().trigger('change');
            $('#deliverables-container').empty();
            $('#project-info-container').slideUp();
        }
    });

    // Display deliverable details when milestone is selected
    $('#milestone-dropdown').on('change', function() {
        var milestoneId = $(this).val();
        var milestoneName = $(this).select2('data')[0] ? $(this).select2('data')[0].text : '';
        
        if (milestoneId) {
            $('#progressreport-milestone_id').val(milestoneId);
            $('#progressreport-milestone_name').val(milestoneName);
            
            // Get deliverable details from stored data
            var deliverables = $(this).data('deliverables');
            if (deliverables) {
                var selectedDeliverable = deliverables.find(d => d.id == milestoneId);
                if (selectedDeliverable) {
                    // Store deliverables data as JSON
                    var deliverablesArray = [selectedDeliverable];
                    $('#progressreport-deliverables_data').val(JSON.stringify(deliverablesArray));
                    
                    // Display deliverable details in info box
                    var html = '<div style=\"background: linear-gradient(135deg, #f0f4f8 0%, #f7fafc 100%); border-left: 4px solid #4299e1; border-radius: 8px; padding: 20px; margin-top: 15px;\">';
                    html += '<div style=\"color: #2c5282; font-weight: 600; margin-bottom: 20px; font-size: 16px; display: flex; align-items: center;\">';
                    html += '<span style=\"font-size: 20px; margin-right: 8px;\">📦</span> Deliverable Details';
                    html += '</div>';
                    
                    html += '<div style=\"display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;\">';
                    
                    // Schedule Name
                    html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
                    html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">Schedule Name</div>';
                    html += '<div style=\"color: #2d3748; font-weight: 500;\">' + (selectedDeliverable.text || '-') + '</div>';
                    html += '</div>';
                    
                    // Details
                    if (selectedDeliverable.details) {
                        html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; grid-column: span 2;\">';
                        html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">Details</div>';
                        html += '<div style=\"color: #2d3748;\">' + selectedDeliverable.details + '</div>';
                        html += '</div>';
                    }
                    
                    // Target Date
                    html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
                    html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">📅 Target Date</div>';
                    html += '<div style=\"color: #2d3748; font-weight: 500;\">' + (selectedDeliverable.target_date || '<em style=\"color: #a0aec0;\">Not set</em>') + '</div>';
                    html += '</div>';
                    
                    // Submission Date
                    html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
                    html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">📤 Submission Date</div>';
                    html += '<div style=\"color: #2d3748; font-weight: 500;\">' + (selectedDeliverable.submission_date || '<em style=\"color: #a0aec0;\">Not submitted</em>') + '</div>';
                    html += '</div>';
                    
                    // Status
                    html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
                    html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">Status</div>';
                    var statusBadge = '';
                    if (selectedDeliverable.status == 1) {
                        statusBadge = '<span style=\"background: #48bb78; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;\">✓ Active</span>';
                    } else if (selectedDeliverable.status == 0) {
                        statusBadge = '<span style=\"background: #cbd5e0; color: #4a5568; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;\">○ Inactive</span>';
                    } else {
                        statusBadge = '<span style=\"color: #a0aec0;\">-</span>';
                    }
                    html += '<div>' + statusBadge + '</div>';
                    html += '</div>';
                    
                    // Budget Allocation
                    if (selectedDeliverable.budget_allocation) {
                        html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
                        html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">💰 Budget Allocation</div>';
                        html += '<div style=\"color: #2d3748; font-weight: 600; font-size: 16px;\">₱ ' + parseFloat(selectedDeliverable.budget_allocation).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</div>';
                        html += '</div>';
                    }
                    
                    // Remarks
                    if (selectedDeliverable.remarks) {
                        html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; grid-column: span 2;\">';
                        html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">📝 Remarks</div>';
                        html += '<div style=\"color: #2d3748;\">' + selectedDeliverable.remarks + '</div>';
                        html += '</div>';
                    }
                    
                    html += '</div>'; // Close grid
                    html += '</div>'; // Close container
                    
                    $('#deliverables-container').html(html);
                }
            }
        } else {
            $('#deliverables-container').html('<div style=\"padding: 10px; color: #718096;\">Select a milestone to view details.</div>');
        }
    });
");

// On page load, if we have an existing project and milestone, load the deliverables
if (!$model->isNewRecord && $model->project_id && $model->milestone_id):
    $this->registerJs("
        $(document).ready(function() {
            var projectId = '" . $model->project_id . "';
            var milestoneId = '" . $model->milestone_id . "';
            var milestoneName = '" . addslashes($model->milestone_name) . "';
            
        console.log('Loading existing data:', projectId, milestoneId, milestoneName);
        
        if (projectId) {
            // Fetch deliverables to populate the dropdown
            $.ajax({
                url: '" . \yii\helpers\Url::to(['get-deliverables']) . "',
                type: 'GET',
                data: { projectId: projectId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.deliverables) {
                        // Populate milestone dropdown with all deliverables
                        var options = [new Option('Select milestone/deliverable...', '', false, false)];
                        $.each(response.deliverables, function(index, deliverable) {
                            var isSelected = (deliverable.id == milestoneId);
                            options.push(new Option(deliverable.text, deliverable.id, isSelected, isSelected));
                        });
                        $('#milestone-dropdown').empty().append(options).trigger('change');
                        
                        // Store deliverables data
                        $('#milestone-dropdown').data('deliverables', response.deliverables);
                        
                        // Display project information
                        if (response.projectData) {
                            var projectInfo = '';
                            var projectData = response.projectData;
                            
                            if (projectData.project_name) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Project Name:</strong> <span style=\"color: #2d3748;\">' + projectData.project_name + '</span></div>';
                            }
                            if (projectData.project_description) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Description:</strong> <span style=\"color: #2d3748;\">' + projectData.project_description + '</span></div>';
                            }
                            if (projectData.project_start_date) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">Start Date:</strong> <span style=\"color: #2d3748;\">' + projectData.project_start_date + '</span></div>';
                            }
                            if (projectData.project_end_date) {
                                projectInfo += '<div style=\"margin-bottom: 10px;\"><strong style=\"color: #5a5a5a;\">End Date:</strong> <span style=\"color: #2d3748;\">' + projectData.project_end_date + '</span></div>';
                            }
                            
                            $('#project-info-content').html(projectInfo || '<em style=\"color: #a0aec0;\">No additional information available</em>');
                            
                            // Display investigators if available
                            var investigatorsHtml = '';
                            if (projectData.investigators && Array.isArray(projectData.investigators) && projectData.investigators.length > 0) {
                                investigatorsHtml = '<div style=\"display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;\">';
                                projectData.investigators.forEach(function(inv) {
                                    investigatorsHtml += '<div style=\"background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
                                    investigatorsHtml += '<div style=\"font-weight: 600; color: #2d3748; margin-bottom: 5px;\">' + (inv.name || 'N/A') + '</div>';
                                    if (inv.role) {
                                        investigatorsHtml += '<div style=\"font-size: 13px; color: #68a57d; margin-bottom: 3px;\">🎯 ' + inv.role + '</div>';
                                    }
                                    if (inv.institution) {
                                        investigatorsHtml += '<div style=\"font-size: 12px; color: #718096;\">🏛️ ' + inv.institution + '</div>';
                                    }
                                    investigatorsHtml += '</div>';
                                });
                                investigatorsHtml += '</div>';
                            } else {
                                investigatorsHtml = '<em style=\"color: #a0aec0;\">No investigators information available</em>';
                            }
                            $('#investigators-content').html(investigatorsHtml);
                            
                            $('#project-info-container').slideDown();
                        }
                        
                        // Now trigger the milestone change to display deliverable details
                        setTimeout(function() {
                            var selectedDeliverable = response.deliverables.find(d => d.id == milestoneId);
                            if (selectedDeliverable) {
                                displayDeliverableDetails(selectedDeliverable);
                            }
                        }, 100);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading existing deliverables:', error);
                }
            });
        }
    });
    ");
endif;

$this->registerJs("
    // Helper function to display deliverable details
    function displayDeliverableDetails(selectedDeliverable) {
        var html = '<div style=\"background: linear-gradient(135deg, #f0f4f8 0%, #f7fafc 100%); border-left: 4px solid #4299e1; border-radius: 8px; padding: 20px; margin-top: 15px;\">';
        html += '<div style=\"color: #2c5282; font-weight: 600; margin-bottom: 20px; font-size: 16px; display: flex; align-items: center;\">';
        html += '<span style=\"font-size: 20px; margin-right: 8px;\">📦</span> Deliverable Details';
        html += '</div>';
        
        html += '<div style=\"display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;\">';
        
        // Schedule Name
        html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
        html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">Schedule Name</div>';
        html += '<div style=\"color: #2d3748; font-weight: 500;\">' + (selectedDeliverable.text || '-') + '</div>';
        html += '</div>';
        
        // Details
        if (selectedDeliverable.details) {
            html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; grid-column: span 2;\">';
            html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">Details</div>';
            html += '<div style=\"color: #2d3748;\">' + selectedDeliverable.details + '</div>';
            html += '</div>';
        }
        
        // Target Date
        html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
        html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">📅 Target Date</div>';
        html += '<div style=\"color: #2d3748; font-weight: 500;\">' + (selectedDeliverable.target_date || '<em style=\"color: #a0aec0;\">Not set</em>') + '</div>';
        html += '</div>';
        
        // Submission Date
        html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
        html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">📤 Submission Date</div>';
        html += '<div style=\"color: #2d3748; font-weight: 500;\">' + (selectedDeliverable.submission_date || '<em style=\"color: #a0aec0;\">Not submitted</em>') + '</div>';
        html += '</div>';
        
        // Status
        html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
        html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">Status</div>';
        var statusBadge = '';
        if (selectedDeliverable.status == 1) {
            statusBadge = '<span style=\"background: #48bb78; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;\">✓ Active</span>';
        } else if (selectedDeliverable.status == 0) {
            statusBadge = '<span style=\"background: #cbd5e0; color: #4a5568; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;\">○ Inactive</span>';
        } else {
            statusBadge = '<span style=\"color: #a0aec0;\">-</span>';
        }
        html += '<div>' + statusBadge + '</div>';
        html += '</div>';
        
        // Budget Allocation
        if (selectedDeliverable.budget_allocation) {
            html += '<div style=\"background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;\">';
            html += '<div style=\"color: #718096; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;\">💰 Budget Allocation</div>';
            html += '<div style=\"color: #2d3748; font-weight: 600; font-size: 16px;\">₱ ' + parseFloat(selectedDeliverable.budget_allocation).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</div>';
            html += '</div>';
        }
        
        html += '</div>'; // Close grid
        html += '</div>'; // Close container
        
        $('#deliverables-container').html(html);
    }");
?>