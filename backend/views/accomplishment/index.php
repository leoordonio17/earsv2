<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchTerm string */
/* @var $workplanGroups common\models\WorkplanGroup[] */
/* @var $selectedGroupId int */

$this->title = 'Accomplishments';
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

    .workplan-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .workplan-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .workplan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(150, 114, 89, 0.3);
        border-color: #967259;
    }

    .workplan-title {
        font-size: 18px;
        font-weight: 600;
        color: #2D1F13;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .workplan-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 15px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
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
        word-wrap: break-word;
        word-break: break-word;
        white-space: normal;
        display: inline-block;
        max-width: 100%;
    }

    .badge-group {
        background: #f3e5f5;
        color: #7b1fa2;
        word-wrap: break-word;
        word-break: break-word;
        white-space: normal;
        display: inline-block;
        max-width: 100%;
    }

    .workplan-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 2px solid #E8D4BC;
    }

    .accomplishment-count {
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

    .search-box {
        margin-bottom: 25px;
    }

    .search-form {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box input {
        flex: 1;
        min-width: 250px;
        padding: 12px 20px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 15px;
    }

    .search-box input:focus {
        outline: none;
        border-color: #967259;
    }

    .search-box select {
        padding: 12px 20px;
        border: 2px solid #D4A574;
        border-radius: 8px;
        font-size: 15px;
        background: white;
        cursor: pointer;
        min-width: 200px;
    }

    .search-box select:focus {
        outline: none;
        border-color: #967259;
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
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-clear:hover {
        background: #967259;
        color: white;
        text-decoration: none;
        transform: translateY(-2px);
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

    @media (max-width: 768px) {
        .workplan-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="accomplishment-container">
    <div class="accomplishment-header">
        <h1>✅ <?= Html::encode($this->title) ?></h1>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="search-box">
        <form method="get" action="<?= \yii\helpers\Url::to(['accomplishment/index']) ?>" class="search-form">
            <select name="group_id">
                <option value="">📁 All Workplan Groups</option>
                <?php foreach ($workplanGroups as $group): ?>
                    <option value="<?= $group->id ?>" <?= $selectedGroupId == $group->id ? 'selected' : '' ?>>
                        <?= Html::encode($group->title) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="text" name="search" placeholder="🔍 Search workplans..." 
                   value="<?= Html::encode($searchTerm ?? '') ?>">
            
            <button type="submit" class="btn-search">Search</button>
            
            <?php if ($searchTerm || $selectedGroupId): ?>
                <?= Html::a('✕ Clear', ['index', 'group_id' => ''], ['class' => 'btn-clear']) ?>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($dataProvider->getTotalCount() > 0): ?>
        <div class="workplan-cards">
            <?php foreach ($dataProvider->models as $workplan): ?>
                <div class="workplan-card" onclick="window.location='<?= \yii\helpers\Url::to(['accomplishment/view-workplan', 'id' => $workplan->id]) ?>'">
                    <div class="workplan-title">
                        📋 <?= Html::encode($workplan->workplan) ?>
                    </div>
                    
                    <div class="workplan-meta">
                        <div class="meta-item">
                            <span class="badge badge-project">
                                <?= Html::encode($workplan->project_name) ?>
                            </span>
                        </div>
                        
                        <?php if ($workplan->workplanGroup): ?>
                            <div class="meta-item">
                                <span class="badge badge-group">
                                    📁 <?= Html::encode($workplan->workplanGroup->title) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            📅 <?= Yii::$app->formatter->asDate($workplan->start_date, 'php:M d, Y') ?> 
                            - <?= Yii::$app->formatter->asDate($workplan->end_date, 'php:M d, Y') ?>
                        </div>
                    </div>
                    
                    <div class="workplan-footer">
                        <div class="accomplishment-count">
                            <?= count($workplan->accomplishments) ?> Accomplishment<?= count($workplan->accomplishments) !== 1 ? 's' : '' ?>
                        </div>
                        <a href="<?= \yii\helpers\Url::to(['accomplishment/view-workplan', 'id' => $workplan->id]) ?>" 
                           class="view-btn" onclick="event.stopPropagation();">
                            View Details →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?= \yii\widgets\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>No Workplans Found</h3>
            <p>
                <?php if ($searchTerm): ?>
                    No workplans match your search. Try different keywords.
                <?php else: ?>
                    You don't have any workplans yet. Create workplans first, then add accomplishments to them.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>
