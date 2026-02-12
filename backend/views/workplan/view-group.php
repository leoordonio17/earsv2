<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $group common\models\WorkplanGroup */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $group->title;
?>
<style>
    .container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .group-header {
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

    .group-title {
        font-size: 32px;
        font-weight: 600;
        color: #2D1F13;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
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

    .group-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #555;
        font-size: 15px;
    }

    .info-label {
        font-weight: 600;
        color: #2D1F13;
    }

    .group-description {
        background: #f8f5f1;
        padding: 15px;
        border-radius: 8px;
        color: #555;
        line-height: 1.6;
        border-left: 4px solid #967259;
    }

    .workplan-list-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        margin-top: 20px;
    }

    .table-wrapper::-webkit-scrollbar {
        height: 8px;
    }

    .table-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: #967259;
        border-radius: 10px;
    }

    .table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #7d5e47;
    }

    .section-title {
        font-size: 22px;
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #E8D4BC;
    }

    .table {
        width: 100%;
        min-width: 1000px;
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

    .table td.project-cell {
        word-wrap: break-word;
        word-break: break-word;
        white-space: normal;
        max-width: 200px;
    }

    .table tr:hover {
        background: #f8f5f1;
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
        word-wrap: break-word;
        word-break: break-word;
        white-space: normal;
        display: inline-block;
        max-width: 100%;
    }

    .badge-task {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .badge-stage {
        background: #e8f5e9;
        color: #388e3c;
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

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        font-size: 22px;
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 8px;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        border: 2px solid #E8D4BC;
        border-radius: 8px;
        font-size: 15px;
    }

    .form-group input:focus {
        outline: none;
        border-color: #967259;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn-modal {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-modal-primary {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
    }

    .btn-modal-secondary {
        background: #f0f0f0;
        color: #666;
    }
</style>

<div class="container">
    <div class="group-header">
        <div class="header-top">
            <div class="group-title">
                📋 <?= Html::encode($group->title) ?>
            </div>
            <div style="display: flex; gap: 10px;">
                <?= Html::a('💾 Save as Template', '#', [
                    'class' => 'btn-back',
                    'style' => 'background: rgba(156, 39, 176, 0.15); border-color: #9c27b0; color: #9c27b0;',
                    'onclick' => 'openTemplateModal(); return false;'
                ]) ?>
                <?= Html::a('✏️ Edit Group', ['update-group', 'id' => $group->id], ['class' => 'btn-back', 'style' => 'background: rgba(25, 118, 210, 0.15); border-color: #1976d2;']) ?>
                <?= Html::a('🗑️ Delete Group', ['delete-group', 'id' => $group->id], [
                    'class' => 'btn-back',
                    'style' => 'background: rgba(211, 47, 47, 0.15); border-color: #d32f2f; color: #d32f2f;',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this workplan group and ALL its workplans?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('← Back', ['index'], ['class' => 'btn-back']) ?>
            </div>
        </div>

        <div class="group-info">
            <div class="info-item">
                <span class="info-label">📅 Start Date:</span>
                <?= Yii::$app->formatter->asDate($group->start_date, 'php:F d, Y') ?>
            </div>
            <div class="info-item">
                <span class="info-label">📅 End Date:</span>
                <?= Yii::$app->formatter->asDate($group->end_date, 'php:F d, Y') ?>
            </div>
            <div class="info-item">
                <span class="info-label">📊 Total Workplans:</span>
                <?= count($group->workplans) ?>
            </div>
        </div>

        <?php if ($group->description): ?>
            <div class="group-description">
                <strong>Description:</strong> <?= Html::encode($group->description) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="workplan-list-section">
        <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span>📝 Workplans in this Group</span>
            <?= Html::a('➕ Add Workplan', ['add-to-group', 'groupId' => $group->id], [
                'class' => 'btn-back',
                'style' => 'background: linear-gradient(135deg, #967259 0%, #B8926A 100%); color: white; border: none; padding: 10px 20px;'
            ]) ?>
        </div>

        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 20%;">Project</th>
                            <th style="width: 15%;">Task Type</th>
                            <th style="width: 15%;">Task Category</th>
                            <th style="width: 10%;">Start Date</th>
                            <th style="width: 10%;">End Date</th>
                            <th style="width: 20%;">Workplan</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $index = 1;
                    foreach ($dataProvider->models as $workplan): 
                    ?>
                        <tr>
                            <td><strong><?= $index++ ?></strong></td>
                            <td class="project-cell">
                                <span class="badge badge-project">
                                    <?= Html::encode($workplan->project_name) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($workplan->taskType): ?>
                                    <span class="badge" style="background: <?= $workplan->taskType->color ?? '#ccc' ?>; color: white;">
                                        <?= Html::encode($workplan->taskType->name) ?>
                                    </span>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($workplan->taskCategory): ?>
                                    <span class="badge" style="background: <?= $workplan->taskCategory->color ?? '#ccc' ?>; color: white;">
                                        <?= Html::encode($workplan->taskCategory->name) ?>
                                    </span>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?= Yii::$app->formatter->asDate($workplan->start_date, 'php:M d, Y') ?></td>
                            <td><?= Yii::$app->formatter->asDate($workplan->end_date, 'php:M d, Y') ?></td>
                            <td>
                                <div style="max-height: 60px; overflow: hidden; line-height: 1.4;">
                                    <?= Html::encode($workplan->workplan) ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <?= Html::a('👁', ['view', 'id' => $workplan->id], [
                                        'class' => 'badge badge-task',
                                        'title' => 'View',
                                        'style' => 'cursor: pointer; text-decoration: none;'
                                    ]) ?>
                                    <?= Html::a('✏️', ['update', 'id' => $workplan->id], [
                                        'class' => 'badge badge-project',
                                        'title' => 'Edit',
                                        'style' => 'cursor: pointer; text-decoration: none;'
                                    ]) ?>
                                    <?= Html::a('🗑️', ['delete', 'id' => $workplan->id], [
                                        'class' => 'badge',
                                        'title' => 'Delete',
                                        'style' => 'cursor: pointer; background: #ffebee; color: #c62828; text-decoration: none;',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to delete this workplan?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h3>No Workplans</h3>
                <p>This group doesn't have any workplans yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Save as Template Modal -->
<div id="templateModal" class="modal-overlay" onclick="if(event.target === this) closeTemplateModal();">
    <div class="modal-content">
        <div class="modal-header">💾 Save as Template</div>
        <form id="templateForm" method="post" action="<?= \yii\helpers\Url::to(['workplan/save-as-template', 'id' => $group->id]) ?>">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
            <div class="form-group">
                <label for="template_name">Template Name <span style="color: red;">*</span></label>
                <input type="text" 
                       id="template_name" 
                       name="template_name" 
                       placeholder="e.g., Monthly Routine Tasks" 
                       required 
                       autofocus>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Give this template a descriptive name so you can easily find it later.
                </small>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeTemplateModal()">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-primary">💾 Save Template</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTemplateModal() {
    document.getElementById('templateModal').style.display = 'flex';
    document.getElementById('template_name').focus();
}

function closeTemplateModal() {
    document.getElementById('templateModal').style.display = 'none';
    document.getElementById('template_name').value = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTemplateModal();
    }
});
</script>
