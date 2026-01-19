<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Accomplishment */

$this->title = 'Create Accomplishment';
?>
<style>
    .form-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .form-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
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
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        color: #E8D4BC;
        text-decoration: none !important;
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h1>✅ <?= Html::encode($this->title) ?></h1>
        <?= Html::a('← Back', ['index'], ['class' => 'btn-back']) ?>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
