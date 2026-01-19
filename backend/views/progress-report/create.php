<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\ProgressReport $model */
/** @var array $assignedProjects */

$this->title = 'Create Progress Report';
$this->params['breadcrumbs'][] = ['label' => 'Progress Reports', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    .create-container {
        padding: 30px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        min-height: 100vh;
    }

    .create-header {
        margin-bottom: 30px;
    }

    .create-header h1 {
        color: white;
        font-size: 32px;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .form-container {
        background: transparent;
    }
</style>

<div class="create-container">
    <div class="create-header">
        <h1>➕ <?= Html::encode($this->title) ?></h1>
    </div>

    <div class="form-container">
        <?= $this->render('_form', [
            'model' => $model,
            'assignedProjects' => $assignedProjects,
        ]) ?>
    </div>
</div>
