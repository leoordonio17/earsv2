<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\ProgressReport $model */
/** @var array $assignedProjects */

$this->title = 'Update Progress Report: ' . Yii::$app->formatter->asDate($model->report_date, 'php:F Y');
$this->params['breadcrumbs'][] = ['label' => 'Progress Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::$app->formatter->asDate($model->report_date, 'php:F Y'), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<style>
    .update-container {
        padding: 30px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        min-height: 100vh;
    }

    .update-header {
        margin-bottom: 30px;
    }

    .update-header h1 {
        color: white;
        font-size: 32px;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .form-container {
        background: transparent;
    }
</style>

<div class="update-container">
    <div class="update-header">
        <h1>✏️ <?= Html::encode($this->title) ?></h1>
    </div>

    <div class="form-container">
        <?= $this->render('_form', [
            'model' => $model,
            'assignedProjects' => $assignedProjects,
        ]) ?>
    </div>
</div>
