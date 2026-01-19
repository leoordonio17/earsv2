<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Workplan */

$this->title = 'Workplan Details';
?>
<style>
    .workplan-view-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .view-header {
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .view-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
    }

    .btn-group {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-back {
        background: rgba(232, 212, 188, 0.2);
        color: #E8D4BC;
        border: 2px solid #E8D4BC;
    }

    .btn-back:hover {
        background: #E8D4BC;
        color: #2D1F13;
        text-decoration: none;
    }

    .btn-edit {
        background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
        color: white;
    }

    .btn-edit:hover {
        background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
        color: white;
        text-decoration: none;
    }

    .btn-delete {
        background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);
        color: white;
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, #c62828 0%, #d32f2f 100%);
        color: white;
        text-decoration: none;
    }

    .detail-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .detail-table {
        width: 100%;
    }

    .detail-table tr {
        border-bottom: 1px solid #E8D4BC;
    }

    .detail-table tr:last-child {
        border-bottom: none;
    }

    .detail-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #2D1F13;
        background: #f8f5f1;
        width: 200px;
    }

    .detail-table td {
        padding: 15px;
        color: #555;
    }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-project {
        background: #e3f2fd;
        color: #1976d2;
    }
</style>

<div class="workplan-view-container">
    <div class="view-header">
        <h1>📋 <?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('← Back to Group', ['view-group', 'id' => $model->workplan_group_id], ['class' => 'btn btn-back']) ?>
            <?= Html::a('✏️ Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-edit']) ?>
            <?= Html::a('🗑️ Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-delete',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this workplan?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="detail-card">
        <table class="detail-table">
            <tr>
                <th>Workplan Group</th>
                <td><?= Html::encode($model->workplanGroup->title) ?></td>
            </tr>
            <tr>
                <th>Project</th>
                <td>
                    <span class="badge badge-project">
                        <?= Html::encode($model->project_name) ?>
                    </span>
                    <br>
                    <small style="color: #967259;">ID: <?= Html::encode($model->project_id) ?></small>
                </td>
            </tr>
            <tr>
                <th>Task Type</th>
                <td>
                    <?php if ($model->taskType): ?>
                        <span class="badge" style="background: <?= $model->taskType->color ?? '#ccc' ?>; color: white;">
                            <?= Html::encode($model->taskType->name) ?>
                        </span>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Task Category</th>
                <td>
                    <?php if ($model->taskCategory): ?>
                        <span class="badge" style="background: <?= $model->taskCategory->color ?? '#ccc' ?>; color: white;">
                            <?= Html::encode($model->taskCategory->name) ?>
                        </span>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Project Stage</th>
                <td>
                    <?php if ($model->projectStage): ?>
                        <span class="badge" style="background: <?= $model->projectStage->color ?? '#ccc' ?>; color: white;">
                            <?= Html::encode($model->projectStage->name) ?>
                        </span>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td><?= Yii::$app->formatter->asDate($model->start_date, 'php:F d, Y') ?></td>
            </tr>
            <tr>
                <th>End Date</th>
                <td><?= Yii::$app->formatter->asDate($model->end_date, 'php:F d, Y') ?></td>
            </tr>
            <tr>
                <th>Workplan Description</th>
                <td style="white-space: pre-wrap;"><?= Html::encode($model->workplan) ?></td>
            </tr>
            <tr>
                <th>Created</th>
                <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
            </tr>
            <tr>
                <th>Last Updated</th>
                <td><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></td>
            </tr>
        </table>
    </div>
</div>
