<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\ProgressReport $model */

$this->title = 'Progress Report - ' . Yii::$app->formatter->asDate($model->report_date, 'php:F Y');
$this->params['breadcrumbs'][] = ['label' => 'Progress Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    .view-container {
        padding: 30px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        min-height: 100vh;
    }

    .view-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .view-header h1 {
        color: white;
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .btn-back {
        padding: 10px 24px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .btn-edit {
        padding: 10px 24px;
        background: white;
        color: #967259;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-edit:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        color: #967259;
        text-decoration: none;
    }

    .btn-delete {
        padding: 10px 24px;
        background: #e53e3e;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-delete:hover {
        background: #c53030;
        transform: translateY(-2px);
    }

    .detail-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
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

    .detail-view th {
        background: #f7fafc;
        color: #2d3748;
        font-weight: 600;
        padding: 12px 16px;
        width: 30%;
        border-bottom: 2px solid #e2e8f0;
    }

    .detail-view td {
        padding: 12px 16px;
        color: #2d3748;
        border-bottom: 1px solid #e2e8f0;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        color: white;
    }

    .extension-badge {
        background: #fed7d7;
        color: #c53030;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .info-box {
        background: #f8f5f1;
        border-left: 4px solid #967259;
        padding: 15px;
        border-radius: 8px;
        margin: 10px 0;
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

    .file-item {
        display: inline-block;
        margin: 5px 10px 5px 0;
        padding: 8px 16px;
        background: #f7fafc;
        border-radius: 8px;
    }

    .file-item a {
        color: #967259;
        text-decoration: none;
        font-weight: 500;
    }

    .file-item a:hover {
        text-decoration: underline;
    }

    .extension-section {
        background: #fff5f5;
        border: 2px solid #feb2b2;
        border-radius: 10px;
        padding: 20px;
        margin-top: 15px;
    }
</style>

<div class="view-container">
    <div class="view-header">
        <h1>👁 <?= Html::encode($this->title) ?></h1>
        <div class="header-actions">
            <?= Html::a('← Back', ['index'], ['class' => 'btn-back']) ?>
            <?= Html::a('✏ Edit', ['update', 'id' => $model->id], ['class' => 'btn-edit']) ?>
            <?= Html::a('🗑 Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn-delete',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this progress report?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="detail-card">
        <div class="section-title">
            📅 Report Information
        </div>

        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'detail-view'],
            'attributes' => [
                'id',
                [
                    'attribute' => 'report_date',
                    'format' => ['date', 'php:F Y'],
                ],
                [
                    'attribute' => 'status',
                    'value' => function($model) {
                        return '<span class="status-badge" style="background: ' . $model->statusColor . ';">' . 
                               Html::encode($model->status) . 
                               '</span>';
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'created_at',
                    'format' => ['datetime', 'php:F d, Y h:i A'],
                ],
                [
                    'attribute' => 'updated_at',
                    'format' => ['datetime', 'php:F d, Y h:i A'],
                ],
            ],
        ]) ?>
    </div>

    <div class="detail-card">
        <div class="section-title">
            🎯 Project Information
        </div>

        <div class="info-box">
            <div class="info-box-content">
                <strong>Project ID:</strong> <?= Html::encode($model->project_id) ?><br>
                <strong>Project Name:</strong> <?= Html::encode($model->project_name) ?><br>
                <?php if ($model->project_data): ?>
                    <hr style="margin: 10px 0; border-color: #cbd5e0;">
                    <?php 
                    $projectData = $model->getProjectDataArray();
                    foreach ($projectData as $key => $value) {
                        echo "<strong>" . Html::encode($key) . ":</strong> " . Html::encode($value) . "<br>";
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($model->milestone_id): ?>
        <div class="detail-card">
            <div class="section-title">
                🎯 Milestone & Deliverables
            </div>

            <div style="background: linear-gradient(135deg, #f0f4f8 0%, #f7fafc 100%); border-left: 4px solid #4299e1; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="color: #2c5282; font-weight: 600; margin-bottom: 15px; font-size: 18px; display: flex; align-items: center;">
                    <span style="font-size: 22px; margin-right: 8px;">📦</span> <?= Html::encode($model->milestone_name) ?>
                </div>
                <div style="color: #4a5568; font-size: 13px;">
                    <strong>Milestone ID:</strong> <?= Html::encode($model->milestone_id) ?>
                </div>
            </div>

            <?php if ($model->deliverables_data): ?>
                <?php 
                $deliverables = $model->getDeliverablesArray();
                foreach ($deliverables as $deliverable): 
                ?>
                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                            
                            <!-- Schedule Name -->
                            <div style="padding: 15px; background: #f7fafc; border-radius: 8px;">
                                <div style="color: #718096; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">Schedule Name</div>
                                <div style="color: #2d3748; font-weight: 600; font-size: 15px;">
                                    <?= Html::encode($deliverable['text'] ?? $deliverable['name'] ?? 'N/A') ?>
                                </div>
                            </div>

                            <!-- Target Date -->
                            <div style="padding: 15px; background: #f7fafc; border-radius: 8px;">
                                <div style="color: #718096; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">📅 Target Date</div>
                                <div style="color: #2d3748; font-weight: 500;">
                                    <?= $deliverable['target_date'] ?? ($deliverable['due_date'] ?? '<em style="color: #a0aec0;">Not set</em>') ?>
                                </div>
                            </div>

                            <!-- Submission Date -->
                            <div style="padding: 15px; background: #f7fafc; border-radius: 8px;">
                                <div style="color: #718096; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">📤 Submission Date</div>
                                <div style="color: #2d3748; font-weight: 500;">
                                    <?= $deliverable['submission_date'] ?? '<em style="color: #a0aec0;">Not submitted</em>' ?>
                                </div>
                            </div>

                            <!-- Status -->
                            <div style="padding: 15px; background: #f7fafc; border-radius: 8px;">
                                <div style="color: #718096; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">Status</div>
                                <div>
                                    <?php 
                                    $status = $deliverable['status'] ?? null;
                                    if ($status == 1): ?>
                                        <span style="background: #48bb78; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">✓ Active</span>
                                    <?php elseif ($status == 0 || $status === '0'): ?>
                                        <span style="background: #cbd5e0; color: #4a5568; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">○ Inactive</span>
                                    <?php else: ?>
                                        <span style="color: #a0aec0;">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Budget Allocation -->
                            <?php if (isset($deliverable['budget_allocation']) && $deliverable['budget_allocation']): ?>
                            <div style="padding: 15px; background: #f7fafc; border-radius: 8px;">
                                <div style="color: #718096; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">💰 Budget Allocation</div>
                                <div style="color: #2d3748; font-weight: 600; font-size: 16px;">
                                    ₱ <?= number_format((float)$deliverable['budget_allocation'], 2) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Details -->
                            <?php if (isset($deliverable['details']) && $deliverable['details']): ?>
                            <div style="padding: 15px; background: #f7fafc; border-radius: 8px; grid-column: span 2;">
                                <div style="color: #718096; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">Details</div>
                                <div style="color: #2d3748; line-height: 1.6;">
                                    <?= Html::encode($deliverable['details']) ?>
                                </div>
                            </div>
                            <?php elseif (isset($deliverable['description']) && $deliverable['description']): ?>
                            <div style="padding: 15px; background: #f7fafc; border-radius: 8px; grid-column: span 2;">
                                <div style="color: #718096; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px;">Description</div>
                                <div style="color: #2d3748; line-height: 1.6;">
                                    <?= Html::encode($deliverable['description']) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($model->has_extension): ?>
        <div class="detail-card">
            <div class="section-title">
                ⚠️ Extension Request
            </div>

            <div class="extension-section">
                <p><span class="extension-badge">⚠️ Extension Required</span></p>
                <br>
                <strong>Proposed Extension Date:</strong> <?= Yii::$app->formatter->asDate($model->extension_date, 'php:F d, Y') ?><br><br>
                <strong>Justification:</strong><br>
                <div style="white-space: pre-wrap; margin-top: 10px; padding: 10px; background: white; border-radius: 8px;">
                    <?= Html::encode($model->extension_justification) ?>
                </div>

                <?php if ($model->documents): ?>
                    <br>
                    <strong>📎 Attached Documents:</strong><br>
                    <?php foreach ($model->getDocumentsArray() as $doc): ?>
                        <div class="file-item">
                            <a href="<?= Yii::$app->request->baseUrl . '/' . $doc ?>" target="_blank">
                                📄 <?= basename($doc) ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
