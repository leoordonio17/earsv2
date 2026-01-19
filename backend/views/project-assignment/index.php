<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $groupedData array */

$this->title = 'Project Assignment Management';
?>
<style>
    .project-assignment-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .project-assignment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .project-assignment-header h1 {
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

    .project-assignment-table-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .project-assignment-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .project-assignment-table thead th {
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

    .project-assignment-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }

    .project-assignment-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }

    .project-assignment-table tbody tr {
        transition: all 0.3s ease;
    }

    .project-assignment-table tbody tr:hover {
        background: #f8f5f1;
        transform: scale(1.01);
    }

    .project-assignment-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #E8D4BC;
        color: #2D1F13;
        font-size: 14px;
        vertical-align: top;
    }

    .project-assignment-table tbody tr:last-child td {
        border-bottom: none;
    }

    .project-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
    }

    .researchers-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .researcher-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f8f5f1;
        border-radius: 12px;
        font-size: 13px;
        color: #2D1F13;
        border: 1px solid #E8D4BC;
    }

    .researcher-count {
        display: inline-block;
        background: #967259;
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 6px;
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

    .btn-edit {
        background: #967259;
        color: white;
    }

    .btn-edit:hover {
        background: #B8926A;
        transform: translateY(-2px);
        color: white;
    }

    .btn-delete-all {
        background: #e74c3c;
        color: white;
    }

    .btn-delete-all:hover {
        background: #c0392b;
        transform: translateY(-2px);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .empty-state-text {
        font-size: 18px;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .empty-state-subtext {
        font-size: 14px;
        color: #adb5bd;
    }
</style>

<div class="project-assignment-container">
    <div class="project-assignment-header">
        <h1>
            <span>📌</span>
            Project Assignment Management
        </h1>
        <?= Html::a('<span>➕</span> Assign Researchers', ['create'], ['class' => 'btn-create']) ?>
    </div>

    <div class="search-container">
        <form method="get" action="<?= Yii::$app->urlManager->createUrl(['project-assignment/index']) ?>" class="search-form">
            <input 
                type="text" 
                name="search" 
                class="search-input" 
                placeholder="🔍 Search by project name or researcher..." 
                value="<?= Html::encode($searchTerm ?? '') ?>"
            />
            <button type="submit" class="btn-search">Search</button>
            <?php if (!empty($searchTerm)): ?>
                <?= Html::a('Clear', ['index'], ['class' => 'btn-clear']) ?>
            <?php endif; ?>
        </form>
        <?php if (!empty($searchTerm)): ?>
            <div style="margin-top: 12px; color: #967259; font-size: 14px;">
                <strong>Search results for:</strong> "<?= Html::encode($searchTerm) ?>" 
                (<?= count($groupedData) ?> project(s) found)
            </div>
        <?php endif; ?>
    </div>

    <div class="project-assignment-table-container">
        <?php if (!empty($groupedData)): ?>
            <table class="project-assignment-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th style="width: 35%;">Project</th>
                        <th style="width: 40%;">Researchers</th>
                        <th style="width: 200px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rowNumber = 1;
                    foreach ($groupedData as $data): 
                    ?>
                        <tr>
                            <td><?= $rowNumber++ ?></td>
                            <td>
                                <div class="project-badge">
                                    <?= Html::encode($data['project_name'] ?: 'N/A') ?>
                                </div>
                            </td>
                            <td>
                                <div class="researchers-list">
                                    <?php foreach ($data['researchers'] as $researcher): ?>
                                        <span class="researcher-tag">
                                            👤 <?= Html::encode($researcher) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <small style="color: #967259; margin-top: 8px; display: block;">
                                    Total: <?= count($data['researchers']) ?> researcher(s)
                                </small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?= Html::a('✏️ Edit', ['update', 'id' => $data['id']], ['class' => 'btn-action btn-edit', 'title' => 'Edit project assignments']) ?>
                                    <?= Html::a('🗑️ Remove All', ['delete-project', 'project_id' => $data['project_id']], [
                                        'class' => 'btn-action btn-delete-all',
                                        'title' => 'Remove all researchers from this project',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to remove ALL researchers from this project?',
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
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-text">No project assignment records found</div>
                <div class="empty-state-subtext">Click "Assign Researchers" to add your first assignment</div>
            </div>
        <?php endif; ?>
    </div>
</div>
