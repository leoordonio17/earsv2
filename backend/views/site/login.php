<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'EARS Login';
$this->registerCssFile('@web/css/login.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="login-container">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
        <div class="gradient-orb orb-3"></div>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <div class="login-header">
            <div class="logo-container">
                <?= Html::img('@web/images/PIDS_logo.png', ['alt' => 'PIDS Logo', 'class' => 'logo']) ?>
            </div>
            <h1 class="project-title">Electronic Accomplishment<br>Reporting System</h1>
            <p class="project-subtitle">EARS</p>
        </div>

        <div class="login-body">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'template' => '{input}{error}',
                    'options' => ['class' => 'form-group'],
                ],
            ]); ?>

            <?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'class' => 'form-control modern-input',
                'placeholder' => 'Enter your username',
                'id' => 'username-input'
            ])->label(false) ?>

            <?= $form->field($model, 'password')->passwordInput([
                'class' => 'form-control modern-input',
                'placeholder' => 'Enter your password',
                'id' => 'password-input'
            ])->label(false) ?>

            <div class="remember-me-container">
                <?= $form->field($model, 'rememberMe')->checkbox([
                    'class' => 'custom-checkbox',
                    'template' => '<div class="checkbox-wrapper">{input} {label}</div>{error}',
                    'labelOptions' => ['class' => 'checkbox-label']
                ]) ?>
            </div>

            <div class="form-group">
                <?= Html::submitButton('<span class="btn-text">Sign In</span><span class="btn-icon">→</span>', [
                    'class' => 'btn-login',
                    'name' => 'login-button'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <div class="login-footer">
            <p class="footer-text">© <?= date('Y') ?> Philippine Institute for Development Studies. All rights reserved. </p>
        </div>
    </div>
</div>
