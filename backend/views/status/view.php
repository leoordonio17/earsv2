<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Status */

$this->title = $model->name;
?>
<style>
    .view-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .view-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .view-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-actions {
        display: flex;
        gap: 10px;
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
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        color: #E8D4BC;
        text-decoration: none;
    }

    .btn-edit {
        padding: 10px 24px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
    }

    .btn-edit:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(150, 114, 89, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-delete {
        padding: 10px 24px;
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-delete:hover {
        background: #c0392b;
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .view-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        max-width: 900px;
        margin: 0 auto;
    }

    .status-preview {
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        text-align: center;
        border: 2px solid #E8D4BC;
    }

    .status-preview-label {
        color: #967259;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }

    .status-badge-large {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 32px;
        border-radius: 30px;
        font-size: 24px;
        font-weight: 600;
        color: white;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table tr {
        border-bottom: 1px solid #E8D4BC;
    }

    .detail-table tr:last-child {
        border-bottom: none;
    }

    .detail-table th {
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        color: #2D1F13;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        text-align: left;
        width: 35%;
    }

    .detail-table td {
        padding: 16px 20px;
        color: #3E2F1F;
        font-size: 15px;
    }

    .color-display {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .color-preview-large {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 3px solid white;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .color-code {
        font-family: monospace;
        font-size: 16px;
        font-weight: 600;
        color: #2D1F13;
    }

    .active-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        display: inline-block;
    }

    .active-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .active-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .timestamp {
        color: #B8926A;
        font-size: 14px;
    }
</style>

<div class="view-container">
    <div class="view-header">
        <h1>
            <span>👁</span>
            View Status
        </h1>
        <div class="header-actions">
            <?= Html::a('<span>←</span> Back', ['index'], ['class' => 'btn-back']) ?>
            <?= Html::a('<span>✏️</span> Edit', ['update', 'id' => $model->id], ['class' => 'btn-edit']) ?>
            <?= Html::a('<span>🗑</span> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn-delete',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this status?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="view-card">
        <div class="status-preview">
            <div class="status-preview-label">Status Preview</div>
            <div class="status-badge-large" style="background-color: <?= Html::encode($model->color) ?>;">
                <?= Html::encode($model->name) ?>
            </div>
        </div>

        <table class="detail-table">
            <tr>
                <th>ID</th>
                <td><?= Html::encode($model->id) ?></td>
            </tr>
            <tr>
                <th>Status Name</th>
                <td><strong style="font-size: 18px;"><?= Html::encode($model->name) ?></strong></td>
            </tr>
            <tr>
                <th>Description</th>
                <td><?= Html::encode($model->description ?: 'No description provided') ?></td>
            </tr>
            <tr>
                <th>Color</th>
                <td>
                    <div class="color-display">
                        <div class="color-preview-large" style="background-color: <?= Html::encode($model->color) ?>;"></div>
                        <span class="color-code"><?= Html::encode($model->color) ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Sort Order</th>
                <td><?= Html::encode($model->sort_order) ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="active-badge <?= $model->is_active ? 'active' : 'inactive' ?>">
                        <?= $model->is_active ? '✓ Active' : '✗ Inactive' ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Created At</th>
                <td class="timestamp"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
            </tr>
            <tr>
                <th>Updated At</th>
                <td class="timestamp"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></td>
            </tr>
        </table>
    </div>
</div>
