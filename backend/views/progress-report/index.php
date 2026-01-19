<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|null $searchQuery */

$this->title = 'Progress Reports';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    .progress-report-index-container {
        padding: 30px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        min-height: 100vh;
    }

    .index-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .index-header h1 {
        color: white;
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .header-actions {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .btn-create {
        padding: 12px 28px;
        background: white;
        color: #967259;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-create:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        color: #967259;
        text-decoration: none;
    }

    .search-box {
        position: relative;
        width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 45px 12px 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 25px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-box input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-box input:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.3);
        border-color: white;
    }

    .search-box button {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.3);
        border: none;
        padding: 8px 15px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-box button:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .report-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #967259 0%, #B8926A 100%);
    }

    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .report-date {
        font-size: 24px;
        font-weight: 700;
        color: #967259;
        margin-bottom: 15px;
    }

    .report-project {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .report-milestone {
        font-size: 14px;
        color: #718096;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .report-status {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        color: white;
        margin-bottom: 15px;
    }

    .report-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
        font-size: 12px;
        color: #a0aec0;
    }

    .extension-badge {
        background: #fed7d7;
        color: #c53030;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .empty-state-icon {
        font-size: 80px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        color: #967259;
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #718096;
        font-size: 16px;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .reports-grid {
            grid-template-columns: 1fr;
        }

        .search-box {
            width: 100%;
        }
    }
</style>

<div class="progress-report-index-container">
    <div class="index-header">
        <h1>📊 <?= Html::encode($this->title) ?></h1>
        <div class="header-actions">
            <form method="get" action="<?= Url::to(['index']) ?>" class="search-box">
                <input type="text" name="search" placeholder="Search reports..." value="<?= Html::encode($searchQuery) ?>">
                <button type="submit">🔍</button>
            </form>
            <?= Html::a('➕ Create Report', ['create'], ['class' => 'btn-create']) ?>
        </div>
    </div>

    <?php if ($dataProvider->getTotalCount() > 0): ?>
        <div class="reports-grid">
            <?php foreach ($dataProvider->models as $model): ?>
                <div class="report-card" onclick="window.location.href='<?= Url::to(['view', 'id' => $model->id]) ?>'">
                    <div class="report-date">
                        📅 <?= Yii::$app->formatter->asDate($model->report_date, 'php:F Y') ?>
                    </div>
                    <div class="report-project">
                        <?= Html::encode($model->project_name) ?>
                    </div>
                    <?php if ($model->milestone_name): ?>
                        <div class="report-milestone">
                            🎯 <?= Html::encode($model->milestone_name) ?>
                        </div>
                    <?php endif; ?>
                    <div class="report-status" style="background: <?= $model->statusColor ?>;">
                        <?= Html::encode($model->status) ?>
                    </div>
                    <div class="report-meta">
                        <span>Updated: <?= Yii::$app->formatter->asRelativeTime($model->updated_at) ?></span>
                        <?php if ($model->has_extension): ?>
                            <span class="extension-badge">⚠️ Extension</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <?= \yii\widgets\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination'],
                'linkOptions' => ['style' => 'color: white; background: rgba(255, 255, 255, 0.2); padding: 8px 12px; margin: 0 4px; border-radius: 5px; text-decoration: none;'],
                'activePageCssClass' => 'active',
                'pageCssClass' => 'page-item',
            ]) ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3>No Progress Reports Yet</h3>
            <p><?= $searchQuery ? 'No reports match your search criteria.' : 'Start by creating your first progress report!' ?></p>
            <?php if ($searchQuery): ?>
                <?= Html::a('Clear Search', ['index'], ['class' => 'btn-create']) ?>
            <?php else: ?>
                <?= Html::a('➕ Create First Report', ['create'], ['class' => 'btn-create']) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
