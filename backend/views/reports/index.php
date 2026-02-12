<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\web\JqueryAsset;

$this->title = 'Reports';
$this->params['breadcrumbs'][] = $this->title;

// Register jQuery
JqueryAsset::register($this);
?>

<style>
    .reports-container {
        padding: 20px;
    }
    
    .reports-header {
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .reports-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
    }
    
    .reports-header p {
        margin: 5px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }
    
    .report-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 2px solid #e9ecef;
    }
    
    .form-section {
        margin-bottom: 25px;
    }
    
    .form-section h3 {
        color: #2D1F13;
        font-size: 18px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #967259;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        color: #3E2F1F;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-group .required {
        color: #dc3545;
    }
    
    .export-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e9ecef;
    }
    
    .btn-export {
        flex: 1;
        padding: 15px 25px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-excel {
        background: linear-gradient(135deg, #217346 0%, #2B9358 100%);
        color: white;
    }
    
    .btn-excel:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(33, 115, 70, 0.4);
        color: white;
    }
    
    .btn-pdf {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .btn-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
        color: white;
    }
    
    .icon {
        font-size: 20px;
    }
    
    .info-box {
        background: #d1ecf1;
        border-left: 4px solid #0c5460;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .info-box p {
        margin: 0;
        color: #0c5460;
        font-size: 14px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .export-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="reports-container">
    <div class="reports-header">
        <h1>📊 Generate Reports</h1>
        <p>Create comprehensive reports in Excel or PDF format</p>
    </div>
    
    <div class="report-card">
        <div class="info-box">
            <p>
                <strong>ℹ️ Information:</strong> 
                Select the report type and date range, then choose your preferred export format.
                <?php if ($isAdmin): ?>
                    As an administrator, you can generate reports for specific personnel or view consolidated reports for all staff.
                <?php endif; ?>
            </p>
        </div>
        
        <?php $form = \yii\widgets\ActiveForm::begin([
            'id' => 'report-form',
            'options' => ['class' => 'report-form'],
        ]); ?>
        
        <div class="form-section">
            <h3>Report Configuration</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Report Type <span class="required">*</span></label>
                    <?php
                    $reportTypes = [
                        'workplan' => 'Workplan Report',
                        'accomplishment' => 'Accomplishment Report',
                        'progress-report' => 'Progress Report',
                    ];
                    
                    // Add project-specific reports
                    if ($isAdmin) {
                        $reportTypes['progress-report-by-project'] = 'Progress Report per Project (Admin)';
                        $reportTypes['progress-report-combined'] = 'Progress Report - All Projects Combined (Admin)';
                    } else {
                        // Personnel can also generate progress report per project
                        $reportTypes['progress-report-by-project'] = 'Progress Report per Project';
                    }
                    ?>
                    <?= Select2::widget([
                        'name' => 'type',
                        'data' => $reportTypes,
                        'options' => [
                            'placeholder' => 'Select report type...',
                            'id' => 'report-type',
                            'required' => true,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>
                </div>
                
                <?php if ($isAdmin): ?>
                <div class="form-group" id="user-select-group">
                    <label>Personnel <span style="color: #6c757d; font-weight: normal;">(Optional - Leave empty for your own report)</span></label>
                    <?= Select2::widget([
                        'name' => 'user_id',
                        'data' => $personnel,
                        'options' => [
                            'placeholder' => 'Select personnel...',
                            'id' => 'user-id',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>
                </div>
                <?php endif; ?>
                
                <div class="form-group" id="project-select-group" style="display:none;">
                    <label>Project <span class="required">*</span></label>
                    <?= Select2::widget([
                        'name' => 'project_id',
                        'data' => $projects,
                        'options' => [
                            'placeholder' => 'Select project...',
                            'id' => 'project-id',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Start Date <span style="color: #6c757d; font-weight: normal;">(Optional)</span></label>
                    <?= DatePicker::widget([
                        'name' => 'start_date',
                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                        'options' => [
                            'placeholder' => 'Select start date...',
                            'id' => 'start-date',
                        ],
                    ]); ?>
                </div>
                
                <div class="form-group">
                    <label>End Date <span style="color: #6c757d; font-weight: normal;">(Optional)</span></label>
                    <?= DatePicker::widget([
                        'name' => 'end_date',
                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                        'options' => [
                            'placeholder' => 'Select end date...',
                            'id' => 'end-date',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
        
        <div class="export-buttons">
            <button type="button" class="btn-export btn-excel" id="export-excel">
                <span class="icon">📗</span>
                <span>Export to Excel</span>
            </button>
            <button type="button" class="btn-export btn-pdf" id="export-pdf">
                <span class="icon">📕</span>
                <span>Export to PDF</span>
            </button>
        </div>
        
        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>
</div>

<?php
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$excelUrl = \yii\helpers\Url::to(['reports/export-excel']);
$pdfUrl = \yii\helpers\Url::to(['reports/export-pdf']);

$js = <<<JS
jQuery(document).ready(function($) {
    // Show/hide project and user fields based on report type
    $('#report-type').on('change', function() {
        var reportType = $(this).val();
        
        if (reportType === 'progress-report-by-project') {
            $('#project-select-group').show();
            $('#user-select-group').hide();
            $('#user-id').val('').trigger('change');
        } else if (reportType === 'progress-report-combined') {
            $('#project-select-group').hide();
            $('#user-select-group').hide();
            $('#project-id').val('').trigger('change');
            $('#user-id').val('').trigger('change');
        } else {
            $('#project-select-group').hide();
            $('#user-select-group').show();
            $('#project-id').val('').trigger('change');
        }
    });
    
    // Export to Excel
    $('#export-excel').on('click', function() {
        var type = $('#report-type').val();
        
        if (!type) {
            alert('Please select a report type');
            return;
        }
        
        if (type === 'progress-report-by-project') {
            var projectId = $('#project-id').val();
            if (!projectId) {
                alert('Please select a project');
                return;
            }
        }
        
        var form = $('<form>', {
            'method': 'POST',
            'action': '$excelUrl'
        });
        
        form.append($('<input>', {
            'type': 'hidden',
            'name': '$csrfParam',
            'value': '$csrfToken'
        }));
        
        form.append($('<input>', {
            'type': 'hidden',
            'name': 'type',
            'value': type
        }));
        
        var projectId = $('#project-id').val();
        if (projectId) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'project_id',
                'value': projectId
            }));
        }
JS;

if ($isAdmin) {
    $js .= <<<JS
        
        var userId = $('#user-id').val();
        if (userId) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'user_id',
                'value': userId
            }));
        }
JS;
}

$js .= <<<JS
        
        var startDate = $('#start-date').val();
        if (startDate) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'start_date',
                'value': startDate
            }));
        }
        
        var endDate = $('#end-date').val();
        if (endDate) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'end_date',
                'value': endDate
            }));
        }
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
    
    // Export to PDF
    $('#export-pdf').on('click', function() {
        var type = $('#report-type').val();
        
        if (!type) {
            alert('Please select a report type');
            return;
        }
        
        if (type === 'progress-report-by-project') {
            var projectId = $('#project-id').val();
            if (!projectId) {
                alert('Please select a project');
                return;
            }
        }
        
        var form = $('<form>', {
            'method': 'POST',
            'action': '$pdfUrl'
        });
        
        form.append($('<input>', {
            'type': 'hidden',
            'name': '$csrfParam',
            'value': '$csrfToken'
        }));
        
        form.append($('<input>', {
            'type': 'hidden',
            'name': 'type',
            'value': type
        }));
        
        var projectId = $('#project-id').val();
        if (projectId) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'project_id',
                'value': projectId
            }));
        }
JS;

if ($isAdmin) {
    $js .= <<<JS
        
        var userId = $('#user-id').val();
        if (userId) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'user_id',
                'value': userId
            }));
        }
JS;
}

$js .= <<<JS
        
        var startDate = $('#start-date').val();
        if (startDate) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'start_date',
                'value': startDate
            }));
        }
        
        var endDate = $('#end-date').val();
        if (endDate) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'end_date',
                'value': endDate
            }));
        }
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
});
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>
