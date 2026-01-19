<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $workplan common\models\Workplan */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Accomplishments';
?>
<style>
    .container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .workplan-header {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .workplan-title {
        font-size: 24px;
        font-weight: 600;
        color: #2D1F13;
        margin: 0;
    }

    .btn-back {
        padding: 10px 24px;
        background: rgba(150, 114, 89, 0.15);
        color: #2D1F13;
        border: 2px solid #967259;
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
        background: #967259;
        color: white;
        transform: translateY(-2px);
        text-decoration: none !important;
    }

    .workplan-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        padding: 20px;
        background: #f8f5f1;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-label {
        font-weight: 600;
        color: #2D1F13;
        font-size: 13px;
    }

    .info-value {
        color: #555;
        font-size: 14px;
    }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-project {
        background: #e3f2fd;
        color: #1976d2;
    }

    .accomplishment-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 22px;
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #E8D4BC;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-add {
        padding: 10px 20px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-add:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #E8D4BC;
        font-size: 14px;
        color: #2D1F13;
    }

    .table tr:hover {
        background: #f8f5f1;
    }

    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }
</style>

<div class="container">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            ✓ <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            ✗ <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="workplan-header">
        <div class="header-top">
            <div class="workplan-title">
                📋 Workplan Details
            </div>
            <?= Html::a('← Back to List', ['index'], ['class' => 'btn-back']) ?>
        </div>

        <div class="workplan-info">
            <div class="info-item">
                <span class="info-label">Workplan</span>
                <span class="info-value"><?= Html::encode($workplan->workplan) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Project</span>
                <span class="badge badge-project"><?= Html::encode($workplan->project_name) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Start Date</span>
                <span class="info-value"><?= Yii::$app->formatter->asDate($workplan->start_date, 'php:M d, Y') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">End Date</span>
                <span class="info-value"><?= Yii::$app->formatter->asDate($workplan->end_date, 'php:M d, Y') ?></span>
            </div>
        </div>
    </div>

    <div class="accomplishment-section">
        <div class="section-title">
            <span>✅ Accomplishments (<?= $dataProvider->getTotalCount() ?>)</span>
            <?= Html::a('➕ Add Accomplishment', ['create', 'workplan_id' => $workplan->id], ['class' => 'btn-add']) ?>
        </div>

        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 22%;">Accomplished Task</th>
                        <th style="width: 10%;">Start Date</th>
                        <th style="width: 10%;">End Date</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Mode</th>
                        <th style="width: 10%;">Milestone</th>
                        <th style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $index = 1;
                    foreach ($dataProvider->models as $accomplishment): 
                    ?>
                        <tr>
                            <td><strong><?= $index++ ?></strong></td>
                            <td>
                                <div style="max-height: 60px; overflow: hidden; line-height: 1.4;">
                                    <?= Html::encode($accomplishment->accomplished_task) ?>
                                </div>
                            </td>
                            <td><?= Yii::$app->formatter->asDate($accomplishment->start_date, 'php:M d, Y') ?></td>
                            <td><?= Yii::$app->formatter->asDate($accomplishment->end_date, 'php:M d, Y') ?></td>
                            <td>
                                <span class="badge badge-status" style="background: <?= $accomplishment->status->color ?? '#ccc' ?>; color: white;">
                                    <?= Html::encode($accomplishment->status->name ?? 'N/A') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($accomplishment->mode_of_delivery): ?>
                                    <span class="badge" style="background: #e8f5e9; color: #2e7d32;">
                                        <?php
                                        $icons = ['Onsite' => '🏢', 'WFH' => '🏠', 'Hybrid' => '🔄'];
                                        echo ($icons[$accomplishment->mode_of_delivery] ?? '') . ' ' . Html::encode($accomplishment->mode_of_delivery);
                                        ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($accomplishment->milestone): ?>
                                    <span class="badge" style="background: <?= $accomplishment->milestone->color ?? '#ccc' ?>; color: white;">
                                        <?= Html::encode($accomplishment->milestone->name) ?>
                                    </span>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <?= Html::a('👁', ['view', 'id' => $accomplishment->id], [
                                        'class' => 'badge',
                                        'title' => 'View',
                                        'style' => 'cursor: pointer; background: #e3f2fd; color: #1976d2; text-decoration: none;'
                                    ]) ?>
                                    <?= Html::a('✏️', ['update', 'id' => $accomplishment->id], [
                                        'class' => 'badge',
                                        'title' => 'Edit',
                                        'style' => 'cursor: pointer; background: #fff3e0; color: #f57c00; text-decoration: none;'
                                    ]) ?>
                                    <?= Html::a('🗑️', ['delete', 'id' => $accomplishment->id], [
                                        'class' => 'badge',
                                        'title' => 'Delete',
                                        'style' => 'cursor: pointer; background: #ffebee; color: #c62828; text-decoration: none;',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to delete this accomplishment?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">✅</div>
                <h3>No Accomplishments Yet</h3>
                <p>Start tracking your progress by adding accomplishments to this workplan.</p>
                <?= Html::a('➕ Add First Accomplishment', ['create', 'workplan_id' => $workplan->id], ['class' => 'btn-add']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
