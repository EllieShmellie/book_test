<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use borales\extensions\phoneInput\PhoneInput;

/* @var $this yii\web\View */
/* @var $model app\models\Subscriber */
/* @var $author app\models\Author */

$this->title = 'Подписка на автора: ' . $author->last_name;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-subscribe">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <p>Введите ваш номер телефона для подписки на обновления автора <?= Html::encode($author->last_name) ?>:</p>
    
    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'phone')->widget(PhoneInput::class, [
            'jsOptions' => [
                'preferredCountries' => ['ru'],
            ]
        ]); 
    ?>
    
    <div class="form-group">
        <?= Html::submitButton('Подписаться', ['class' => 'btn btn-primary']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
</div>
