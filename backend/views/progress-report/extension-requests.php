<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statusFilter string|null */
/* @var $monthFilter int|null */
/* @var $yearFilter int|null */

$this->title = 'Extension Requests';
?>

<style>
    .extension-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .extension-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .extension-title {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-back {
        padding: 10px 20px;
        background: #B8926A;
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-back:hover {
        background: #967259;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        color: white;
        text-decoration: none;
    }

    .extensions-table-wrapper {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .extension-cards {
        display: grid;
        gap: 20px;
    }

    .extension-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 15px;
        padding: 25px;
        transition: all 0.3s ease;
    }

    .extension-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(150, 114, 89, 0.2);
        border-color: #967259;
    }

    .extension-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .extension-card-title {
        flex: 1;
    }

    .extension-project-name {
        font-size: 18px;
        font-weight: 600;
        color: #2d1f13;
        margin-bottom: 5px;
    }

    .extension-milestone {
        font-size: 13px;
        color: #666;
    }

    .extension-card-body {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .extension-info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .extension-info-label {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .extension-info-value {
        font-size: 14px;
        color: #2d1f13;
        font-weight: 500;
    }

    .extension-justification {
        background: #f8f5f1;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #ff9800;
    }

    .extension-justification-label {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .extension-justification-text {
        font-size: 14px;
        color: #2d1f13;
        line-height: 1.6;
        max-height: 60px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .extension-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 2px solid #f0f0f0;
    }

    .extension-processed-info {
        font-size: 12px;
        color: #666;
    }

    .grid-view {
        display: none;
    }

    .grid-view table {
        width: 100%;
        border-collapse: collapse;
    }

    .grid-view th {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .grid-view td {
        padding: 18px 15px;
        border-bottom: 1px solid #e0e0e0;
        font-size: 14px;
        vertical-align: middle;
    }

    .grid-view tbody tr {
        transition: all 0.3s ease;
    }

    .grid-view tbody tr:hover {
        background: #f8f5f1;
        transform: scale(1.01);
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-pending {
        background: #fff3e0;
        color: #e65100;
    }

    .status-approved {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-rejected {
        background: #ffebee;
        color: #c62828;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-process {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-approve {
        background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
        color: white;
    }

    .btn-approve:hover {
        background: linear-gradient(135deg, #45a049 0%, #57a05a 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
    }

    .btn-reject {
        background: linear-gradient(135deg, #f44336 0%, #e57373 100%);
        color: white;
    }

    .btn-reject:hover {
        background: linear-gradient(135deg, #da190b 0%, #d32f2f 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3);
    }

    .btn-view {
        background: linear-gradient(135deg, #2196f3 0%, #64b5f6 100%);
        color: white;
    }

    .btn-view:hover {
        background: linear-gradient(135deg, #0b7dda 0%, #1976d2 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(33, 150, 243, 0.3);
        color: white;
        text-decoration: none;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background: white;
        margin: 10% auto;
        padding: 30px;
        border-radius: 15px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #2d1f13;
        padding-bottom: 15px;
        border-bottom: 2px solid #e0e0e0;
    }

    .modal-body {
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #2d1f13;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-control:focus {
        border-color: #967259;
        outline: none;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px solid #e0e0e0;
    }

    .btn-cancel {
        background: linear-gradient(135deg, #999 0%, #888 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background: linear-gradient(135deg, #777 0%, #666 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        list-style: none;
        padding: 0;
    }

    .pagination li a,
    .pagination li span {
        padding: 8px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        text-decoration: none;
        color: #2d1f13;
        transition: all 0.3s ease;
    }

    .pagination li.active span {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border-color: #967259;
    }

    .pagination li a:hover {
        background: #f8f5f1;
        border-color: #967259;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-label {
        font-weight: 600;
        color: #2d1f13;
        font-size: 14px;
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        color: #2d1f13;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 200px;
    }

    .filter-select:focus {
        border-color: #967259;
        outline: none;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    .filter-select:hover {
        border-color: #B8926A;
    }

    .btn-reset-filter {
        padding: 10px 20px;
        background: #e0e0e0;
        color: #666;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-reset-filter:hover {
        background: #d0d0d0;
        color: #444;
        text-decoration: none;
    }
</style>

<div class="extension-container">
    <div class="extension-header">
        <h1 class="extension-title">📋 Extension Requests</h1>
        <?= Html::a('← Back to Reports', ['index'], ['class' => 'btn-back']) ?>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <span class="filter-label">🔍 Filters:</span>
        
        <select class="filter-select" id="statusFilter" onchange="applyFilter()">
            <option value="" <?= $statusFilter === null ? 'selected' : '' ?>>All Statuses</option>
            <option value="<?= \common\models\ProgressReport::EXTENSION_PENDING ?>" <?= $statusFilter === \common\models\ProgressReport::EXTENSION_PENDING ? 'selected' : '' ?>>Pending</option>
            <option value="<?= \common\models\ProgressReport::EXTENSION_APPROVED ?>" <?= $statusFilter === \common\models\ProgressReport::EXTENSION_APPROVED ? 'selected' : '' ?>>Approved</option>
            <option value="<?= \common\models\ProgressReport::EXTENSION_REJECTED ?>" <?= $statusFilter === \common\models\ProgressReport::EXTENSION_REJECTED ? 'selected' : '' ?>>Rejected</option>
        </select>

        <select class="filter-select" id="monthFilter" onchange="applyFilter()">
            <option value="" <?= $monthFilter === null ? 'selected' : '' ?>>All Months</option>
            <option value="1" <?= $monthFilter == 1 ? 'selected' : '' ?>>January</option>
            <option value="2" <?= $monthFilter == 2 ? 'selected' : '' ?>>February</option>
            <option value="3" <?= $monthFilter == 3 ? 'selected' : '' ?>>March</option>
            <option value="4" <?= $monthFilter == 4 ? 'selected' : '' ?>>April</option>
            <option value="5" <?= $monthFilter == 5 ? 'selected' : '' ?>>May</option>
            <option value="6" <?= $monthFilter == 6 ? 'selected' : '' ?>>June</option>
            <option value="7" <?= $monthFilter == 7 ? 'selected' : '' ?>>July</option>
            <option value="8" <?= $monthFilter == 8 ? 'selected' : '' ?>>August</option>
            <option value="9" <?= $monthFilter == 9 ? 'selected' : '' ?>>September</option>
            <option value="10" <?= $monthFilter == 10 ? 'selected' : '' ?>>October</option>
            <option value="11" <?= $monthFilter == 11 ? 'selected' : '' ?>>November</option>
            <option value="12" <?= $monthFilter == 12 ? 'selected' : '' ?>>December</option>
        </select>

        <select class="filter-select" id="yearFilter" onchange="applyFilter()">
            <option value="" <?= $yearFilter === null ? 'selected' : '' ?>>All Years</option>
            <?php
            $currentYear = date('Y');
            for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                <option value="<?= $y ?>" <?= $yearFilter == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        
        <?php if ($statusFilter !== null || $monthFilter !== null || $yearFilter !== null): ?>
            <?= Html::a('✕ Clear All Filters', ['extension-requests'], ['class' => 'btn-reset-filter']) ?>
        <?php endif; ?>
    </div>

    <div class="extensions-table-wrapper">
        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <div class="extension-cards">
                <?php foreach ($dataProvider->models as $model): ?>
                    <div class="extension-card">
                        <div class="extension-card-header">
                            <div class="extension-card-title">
                                <div class="extension-project-name">
                                    <?= Html::encode($model->project_name) ?>
                                </div>
                                <div class="extension-milestone">
                                    📍 <?= Html::encode($model->milestone_name ?? 'No milestone') ?>
                                </div>
                            </div>
                            <div>
                                <?= $model->extensionStatusBadge ?>
                            </div>
                        </div>

                        <div class="extension-card-body">
                            <div class="extension-info-item">
                                <div class="extension-info-label">Report ID</div>
                                <div class="extension-info-value">#<?= $model->id ?></div>
                            </div>

                            <div class="extension-info-item">
                                <div class="extension-info-label">Requested By</div>
                                <div class="extension-info-value">
                                    👤 <?= $model->user ? Html::encode($model->user->username) : 'N/A' ?>
                                </div>
                            </div>

                            <div class="extension-info-item">
                                <div class="extension-info-label">Proposed Extension Date</div>
                                <div class="extension-info-value">
                                    📅 <?= Yii::$app->formatter->asDate($model->extension_date, 'php:M d, Y') ?>
                                </div>
                            </div>

                            <div class="extension-info-item">
                                <div class="extension-info-label">Requested On</div>
                                <div class="extension-info-value">
                                    🕒 <?= Yii::$app->formatter->asDate($model->created_at, 'php:M d, Y') ?>
                                </div>
                            </div>
                        </div>

                        <div class="extension-justification">
                            <div class="extension-justification-label">Justification</div>
                            <div class="extension-justification-text">
                                <?= nl2br(Html::encode($model->extension_justification)) ?>
                            </div>
                        </div>

                        <div class="extension-card-footer">
                            <div class="extension-processed-info">
                                <?php if ($model->extension_status !== \common\models\ProgressReport::EXTENSION_PENDING): ?>
                                    <?php 
                                    $processedAt = $model->extension_processed_at ? Yii::$app->formatter->asDatetime($model->extension_processed_at) : 'N/A';
                                    $processedBy = $model->processor ? Html::encode($model->processor->username) : 'Unknown';
                                    ?>
                                    <div style="font-size: 12px; color: #666;">
                                        Processed by <strong><?= $processedBy ?></strong> on <?= $processedAt ?>
                                    </div>
                                    <?php if ($model->extension_status === \common\models\ProgressReport::EXTENSION_APPROVED): ?>
                                        <div style="font-size: 12px; color: #2e7d32; margin-top: 5px;">
                                            ✓ Approved Date: <strong><?= Yii::$app->formatter->asDate($model->extension_approved_date, 'php:M d, Y') ?></strong>
                                        </div>
                                    <?php elseif ($model->extension_status === \common\models\ProgressReport::EXTENSION_REJECTED): ?>
                                        <div style="font-size: 12px; color: #c62828; margin-top: 5px;">
                                            ✗ Reason: <?= Html::encode($model->extension_rejection_reason) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="action-buttons">
                                <?php if ($model->extension_status === \common\models\ProgressReport::EXTENSION_PENDING): ?>
                                    <?= Html::button('✓ Approve', [
                                        'class' => 'btn-process btn-approve',
                                        'onclick' => "openApproveModal(" . $model->id . ", '" . Html::encode($model->extension_date) . "')"
                                    ]) ?>
                                    <?= Html::button('✗ Reject', [
                                        'class' => 'btn-process btn-reject',
                                        'onclick' => "openRejectModal(" . $model->id . ")"
                                    ]) ?>
                                <?php endif; ?>
                                <?= Html::a('👁 View Report', ['view', 'id' => $model->id], [
                                    'class' => 'btn-process btn-view',
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 20px;">
                <?= \yii\widgets\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination'],
                ]) ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No Extension Requests</h3>
                <p>There are no extension requests at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">✓ Approve Extension Request</div>
        <form id="approveForm" method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <input type="hidden" name="action" value="approve">
            <input type="hidden" id="approve_report_id" name="report_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Approved Extension Date <span style="color: red;">*</span></label>
                    <?= DatePicker::widget([
                        'name' => 'approved_date',
                        'id' => 'approved_date',
                        'options' => ['class' => 'form-control', 'required' => true],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ]); ?>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-process btn-cancel" onclick="closeModal('approveModal')">Cancel</button>
                <button type="submit" class="btn-process btn-approve">✓ Approve</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">✗ Reject Extension Request</div>
        <form id="rejectForm" method="post">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <input type="hidden" name="action" value="reject">
            <input type="hidden" id="reject_report_id" name="report_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label>Rejection Reason <span style="color: red;">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Please provide a reason for rejection..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-process btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
                <button type="submit" class="btn-process btn-reject">✗ Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function applyFilter() {
    const status = document.getElementById('statusFilter').value;
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;
    const url = '<?= \yii\helpers\Url::to(['extension-requests']) ?>';
    
    const params = [];
    if (status) params.push('status=' + encodeURIComponent(status));
    if (month) params.push('month=' + encodeURIComponent(month));
    if (year) params.push('year=' + encodeURIComponent(year));
    
    if (params.length > 0) {
        window.location.href = url + '?' + params.join('&');
    } else {
        window.location.href = url;
    }
}

function openApproveModal(id, proposedDate) {
    document.getElementById('approve_report_id').value = id;
    document.getElementById('approved_date').value = proposedDate;
    document.getElementById('approveModal').style.display = 'block';
    document.getElementById('approveForm').action = '<?= \yii\helpers\Url::to(['process-extension']) ?>?id=' + id;
}

function openRejectModal(id) {
    document.getElementById('reject_report_id').value = id;
    document.getElementById('rejectModal').style.display = 'block';
    document.getElementById('rejectForm').action = '<?= \yii\helpers\Url::to(['process-extension']) ?>?id=' + id;
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
