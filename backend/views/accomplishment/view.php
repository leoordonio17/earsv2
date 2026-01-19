<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Accomplishment */

$this->title = 'Accomplishment Details: ' . $model->id;
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
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-edit:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }

    .btn-delete {
        padding: 10px 24px;
        background: #c92a2a;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-delete:hover {
        background: #a61e1e;
        transform: translateY(-2px);
    }

    .detail-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .detail-view table {
        width: 100%;
    }

    .detail-view th {
        background: #f8f5f1;
        color: #2D1F13;
        font-weight: 600;
        padding: 12px 16px;
        width: 30%;
        border-bottom: 2px solid #E8D4BC;
    }

    .detail-view td {
        padding: 12px 16px;
        color: #2D1F13;
        border-bottom: 1px solid #E8D4BC;
    }

    .badge-yes {
        display: inline-block;
        padding: 4px 12px;
        background: #D4A574;
        color: #2D1F13;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-within {
        display: inline-block;
        padding: 4px 12px;
        background: #51cf66;
        color: white;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-delayed {
        display: inline-block;
        padding: 4px 12px;
        background: #ff6b6b;
        color: white;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
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
                    'confirm' => 'Are you sure you want to delete this accomplishment?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="detail-card">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'detail-view'],
            'attributes' => [
                'id',
                [
                    'attribute' => 'workplan_id',
                    'value' => function($model) {
                        return $model->workplan->project_name . ' (' . 
                               Yii::$app->formatter->asDate($model->workplan->start_date, 'php:M d') . ' - ' .
                               Yii::$app->formatter->asDate($model->workplan->end_date, 'php:M d, Y') . ')';
                    },
                ],
                [
                    'attribute' => 'start_date',
                    'format' => ['date', 'php:F d, Y'],
                ],
                [
                    'attribute' => 'end_date',
                    'format' => ['date', 'php:F d, Y'],
                ],
                [
                    'attribute' => 'accomplished_task',
                    'format' => 'ntext',
                ],
                [
                    'attribute' => 'status_id',
                    'value' => function($model) {
                        if ($model->status) {
                            return '<span class="badge" style="background: ' . ($model->status->color ?? '#ccc') . '; color: white; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">' . 
                                   Html::encode($model->status->name) . 
                                   '</span>';
                        }
                        return 'N/A';
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'mode_of_delivery',
                    'value' => $model->mode_of_delivery ?: '-',
                ],
                [
                    'attribute' => 'is_final_task',
                    'value' => $model->is_final_task ? '<span class="badge-yes">✓ Yes</span>' : 'No',
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'milestone_id',
                    'value' => function($model) {
                        if ($model->milestone) {
                            return '<span class="badge" style="background: ' . ($model->milestone->color ?? '#ccc') . '; color: white; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;">' . 
                                   Html::encode($model->milestone->name) . 
                                   '</span>';
                        }
                        return '-';
                    },
                    'format' => 'raw',
                    'visible' => $model->is_final_task,
                ],
                [
                    'attribute' => 'target_deadline',
                    'format' => ['date', 'php:F d, Y'],
                    'visible' => $model->is_final_task,
                ],
                [
                    'attribute' => 'actual_submission_date',
                    'format' => ['date', 'php:F d, Y'],
                    'visible' => $model->is_final_task,
                ],
                [
                    'attribute' => 'within_target',
                    'value' => function($model) {
                        if ($model->within_target === null) return '-';
                        return $model->within_target 
                            ? '<span class="badge-within">✓ Within Target</span>' 
                            : '<span class="badge-delayed">✗ Delayed</span>';
                    },
                    'format' => 'raw',
                    'visible' => $model->is_final_task,
                ],
                [
                    'attribute' => 'reason_for_delay',
                    'format' => 'ntext',
                    'visible' => $model->is_final_task && !$model->within_target,
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
</div>
