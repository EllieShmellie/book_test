<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use borales\extensions\phoneInput\PhoneInput;

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Заполните, пожалуйста, следующие поля для регистрации:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'form-signup',
    ]); ?>

    <?= $form->field($model, 'phone')->widget(PhoneInput::class, [
        'jsOptions' => [
            'preferredCountries' => ['ru'],
        ]
    ]); ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'password_repeat')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>