<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Workplan */

$this->title = 'Update Workplan';
?>
<style>
    .update-workplan-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
    }

    .btn-back {
        padding: 10px 24px;
        background: rgba(232, 212, 188, 0.2);
        color: #E8D4BC;
        border: 2px solid #E8D4BC;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: #E8D4BC;
        color: #2D1F13;
        text-decoration: none;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
</style>

<div class="update-workplan-container">
    <div class="page-header">
        <h1>✏️ <?= Html::encode($this->title) ?></h1>
        <?= Html::a('← Back to Group', ['view-group', 'id' => $model->workplan_group_id], ['class' => 'btn-back']) ?>
    </div>

    <div class="form-card">
        <?= $this->render('_form_single', [
            'model' => $model,
        ]) ?>
    </div>
</div>
