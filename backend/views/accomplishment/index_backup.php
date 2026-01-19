<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchTerm string */

$this->title = 'My Accomplishments';
?>
<style>
    .accomplishment-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .accomplishment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .accomplishment-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-create {
        padding: 12px 28px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
    }

    .btn-create:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(150, 114, 89, 0.4);
        color: white;
        text-decoration: none;
    }

    .search-container {
        margin-bottom: 25px;
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .search-form {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .search-input {
        flex: 1;
        padding: 12px 18px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 15px;
        color: #2D1F13;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #967259;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    .btn-search {
        padding: 12px 24px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .btn-search:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
    }

    .btn-clear {
        padding: 12px 24px;
        background: white;
        color: #967259;
        border: 2px solid #967259;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-clear:hover {
        background: #967259;
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .accomplishment-table-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .accomplishment-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .accomplishment-table thead th {
        background: linear-gradient(135deg, #3E2F1F 0%, #2D1F13 100%);
        color: #E8D4BC;
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .accomplishment-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }

    .accomplishment-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }

    .accomplishment-table tbody tr {
        transition: all 0.3s ease;
    }

    .accomplishment-table tbody tr:hover {
        background: #f8f5f1;
        transform: scale(1.01);
    }

    .accomplishment-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #E8D4BC;
        color: #2D1F13;
        font-size: 14px;
    }

    .accomplishment-table tbody tr:last-child td {
        border-bottom: none;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view {
        background: #B8926A;
        color: white;
    }

    .btn-view:hover {
        background: #967259;
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .btn-edit {
        background: #967259;
        color: white;
    }

    .btn-edit:hover {
        background: #2D1F13;
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: #c92a2a;
        color: white;
    }

    .btn-delete:hover {
        background: #a61e1e;
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-final {
        background: #D4A574;
        color: #2D1F13;
    }

    .badge-status {
        background: #B8926A;
        color: white;
    }

    .date-range {
        color: #967259;
        font-size: 13px;
    }

    .task-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #967259;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .empty-state-text {
        font-size: 18px;
        font-weight: 500;
        margin-bottom: 10px;
        color: #2D1F13;
    }

    .empty-state-subtext {
        font-size: 14px;
        color: #967259;
    }

    @media (max-width: 768px) {
        .accomplishment-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .search-form {
            flex-direction: column;
        }

        .action-buttons {
            flex-direction: column;
        }

        .task-preview {
            max-width: 150px;
        }
    }
</style>

<div class="accomplishment-container">
    <div class="accomplishment-header">
        <h1>✅ <?= Html::encode($this->title) ?></h1>
        <?= Html::a('➕ Create Accomplishment', ['create'], ['class' => 'btn-create']) ?>
    </div>

    <div class="search-container">
        <form method="get" class="search-form">
            <input type="text" 
                   name="search" 
                   class="search-input" 
                   placeholder="Search by task description or project..." 
                   value="<?= Html::encode($searchTerm ?? '') ?>">
            <button type="submit" class="btn-search">🔍 Search</button>
            <?php if ($searchTerm): ?>
                <?= Html::a('✕ Clear', ['index'], ['class' => 'btn-clear']) ?>
            <?php endif; ?>
        </form>
    </div>

    <div class="accomplishment-table-container">
        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <table class="accomplishment-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 20%;">Project</th>
                        <th style="width: 25%;">Accomplished Task</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Date Range</th>
                        <th style="width: 10%;">Final Task</th>
                        <th style="width: 10%;">Milestone</th>
                        <th style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr>
                            <td><?= Html::encode($model->id) ?></td>
                            <td>
                                <strong><?= Html::encode($model->workplan->project_name) ?></strong><br>
                                <small style="color: #967259;"><?= Html::encode($model->workplan->project_id) ?></small>
                            </td>
                            <td>
                                <div class="task-preview" title="<?= Html::encode($model->accomplished_task) ?>">
                                    <?= Html::encode($model->accomplished_task) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-status">
                                    <?= Html::encode($model->status->name) ?>
                                </span>
                            </td>
                            <td>
                                <div class="date-range">
                                    <?= Yii::$app->formatter->asDate($model->start_date, 'php:M d') ?> -<br>
                                    <?= Yii::$app->formatter->asDate($model->end_date, 'php:M d, Y') ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($model->is_final_task): ?>
                                    <span class="badge badge-final">✓ Yes</span>
                                <?php else: ?>
                                    <span style="color: #967259;">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($model->is_final_task && $model->milestone): ?>
                                    <?= Html::encode($model->milestone->name) ?>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?= Html::a('👁', ['view', 'id' => $model->id], [
                                        'class' => 'btn-action btn-view',
                                        'title' => 'View',
                                    ]) ?>
                                    <?= Html::a('✏', ['update', 'id' => $model->id], [
                                        'class' => 'btn-action btn-edit',
                                        'title' => 'Edit',
                                    ]) ?>
                                    <?= Html::a('🗑', ['delete', 'id' => $model->id], [
                                        'class' => 'btn-action btn-delete',
                                        'title' => 'Delete',
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
                <div class="empty-state-text">No accomplishments found</div>
                <div class="empty-state-subtext">
                    <?php if ($searchTerm): ?>
                        No results match your search. Try different keywords.
                    <?php else: ?>
                        Create your first accomplishment to get started.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
