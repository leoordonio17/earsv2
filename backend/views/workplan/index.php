<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchTerm string */
/* @var $currentView string */

$this->title = 'Workplan Management';
$isTemplateView = ($currentView === 'templates');
?>
<style>
    .workplan-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .workplan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .workplan-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .workplan-groups {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .group-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .group-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(150, 114, 89, 0.3);
        border-color: #967259;
    }

    .group-title {
        font-size: 20px;
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .group-dates {
        color: #666;
        font-size: 14px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .group-description {
        color: #555;
        font-size: 14px;
        margin-bottom: 15px;
        line-height: 1.5;
        max-height: 60px;
        overflow: hidden;
    }

    .group-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 2px solid #E8D4BC;
    }

    .workplan-count {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .view-btn {
        color: #967259;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }

    .view-btn:hover {
        color: #2D1F13;
        text-decoration: none;
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

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: #2D1F13;
        font-size: 22px;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #666;
        font-size: 15px;
        margin-bottom: 25px;
    }

    .search-box {
        margin-bottom: 25px;
    }

    .search-box input {
        width: 100%;
        max-width: 400px;
        padding: 12px 20px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 15px;
    }

    .search-box input:focus {
        outline: none;
        border-color: #967259;
    }

    .view-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #E8D4BC;
        padding-bottom: 5px;
    }

    .view-tab {
        padding: 10px 24px;
        background: white;
        border: 2px solid #E8D4BC;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        color: #666;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        bottom: -2px;
    }

    .view-tab:hover {
        background: #f8f5f1;
        text-decoration: none;
        color: #2D1F13;
    }

    .view-tab.active {
        background: #967259;
        border-color: #967259;
        color: white;
        font-weight: 600;
    }

    .template-badge {
        background: #967259;
        color: white;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 10px;
        margin-left: 8px;
        font-weight: 600;
    }

    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .delete-btn:hover {
        background: #c82333;
        transform: scale(1.05);
    }

    .template-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    @media (max-width: 768px) {
        .workplan-groups {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="workplan-container">
    <div class="workplan-header">
        <h1>📋 <?= Html::encode($this->title) ?></h1>
        <?php if ($isTemplateView): ?>
            <?= Html::a('⬅ Back to Workplans', ['index', 'view' => 'groups'], ['class' => 'btn-create']) ?>
        <?php else: ?>
            <?= Html::a('➕ Create New Workplan Group', ['create'], ['class' => 'btn-create']) ?>
        <?php endif; ?>
    </div>

    <div class="view-tabs">
        <?= Html::a(
            '📋 My Workplan Groups',
            ['index', 'view' => 'groups'],
            ['class' => 'view-tab' . ($currentView === 'groups' ? ' active' : '')]
        ) ?>
        <?= Html::a(
            '📚 My Templates',
            ['index', 'view' => 'templates'],
            ['class' => 'view-tab' . ($isTemplateView ? ' active' : '')]
        ) ?>
    </div>

    <div class="search-box">
        <form method="get" action="<?= \yii\helpers\Url::to(['workplan/index']) ?>">
            <input type="hidden" name="view" value="<?= Html::encode($currentView) ?>">
            <input type="text" name="search" placeholder="🔍 Search <?= $isTemplateView ? 'templates' : 'workplan groups' ?>..." 
                   value="<?= Html::encode($searchTerm ?? '') ?>">
        </form>
    </div>

    <?php if ($dataProvider->getTotalCount() > 0): ?>
        <div class="workplan-groups">
            <?php foreach ($dataProvider->models as $group): ?>
                <div class="group-card" onclick="window.location='<?= $isTemplateView ? \yii\helpers\Url::to(['workplan/create-from-template', 'templateId' => $group->id]) : \yii\helpers\Url::to(['workplan/view-group', 'id' => $group->id]) ?>'">
                    <div class="group-title">
                        <?= $isTemplateView ? '📚' : '📋' ?> <?= Html::encode($isTemplateView && $group->template_name ? $group->template_name : $group->title) ?>
                        <?php if ($isTemplateView): ?>
                            <span class="template-badge">TEMPLATE</span>
                        <?php endif; ?>
                    </div>
                    <div class="group-dates">
                        📅 <?= Yii::$app->formatter->asDate($group->start_date, 'php:M d, Y') ?> 
                        - <?= Yii::$app->formatter->asDate($group->end_date, 'php:M d, Y') ?>
                    </div>
                    <?php if ($group->description): ?>
                        <div class="group-description">
                            <?= Html::encode($group->description) ?>
                        </div>
                    <?php endif; ?>
                    <div class="group-footer">
                        <div class="workplan-count">
                            <?= count($group->workplans) ?> Workplan<?= count($group->workplans) !== 1 ? 's' : '' ?>
                        </div>
                        <?php if ($isTemplateView): ?>
                            <div class="template-actions">
                                <a href="<?= \yii\helpers\Url::to(['workplan/create-from-template', 'templateId' => $group->id]) ?>" 
                                   class="view-btn" onclick="event.stopPropagation();">
                                    Use Template →
                                </a>
                                <?= Html::beginForm(['delete-group', 'id' => $group->id], 'post', ['style' => 'display:inline;', 'onclick' => 'event.stopPropagation();']) ?>
                                    <button type="submit" class="delete-btn" 
                                            onclick="return confirm('Are you sure you want to delete this template? This action cannot be undone.');">
                                        🗑️ Delete
                                    </button>
                                <?= Html::endForm() ?>
                            </div>
                        <?php else: ?>
                            <a href="<?= \yii\helpers\Url::to(['workplan/view-group', 'id' => $group->id]) ?>" 
                               class="view-btn" onclick="event.stopPropagation();">
                                View Details →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?= \yii\widgets\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon"><?= $isTemplateView ? '📚' : '📋' ?></div>
            <h3><?= $isTemplateView ? 'No Templates Yet' : 'No Workplan Groups Yet' ?></h3>
            <p><?= $isTemplateView 
                ? 'Save your frequently used workplan groups as templates for quick reuse!' 
                : 'Get started by creating your first workplan group. You can organize multiple workplans under one group!' ?>
            </p>
            <?php if (!$isTemplateView): ?>
                <?= Html::a('➕ Create First Workplan Group', ['create'], ['class' => 'btn-create']) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
