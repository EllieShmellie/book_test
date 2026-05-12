<?php
/* @var $this yii\web\View */
/* @var $model app\models\Book */
/* @var $authors app\models\Author[] */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

$authorsList = ArrayHelper::map($authors, 'author_id', 'last_name');
$model->author_ids = ArrayHelper::getColumn($model->authors, 'author_id');

?>

<div class="book-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'year')->textInput() ?>
    
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
    
    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'cover_file')->fileInput() ?>
    
    <?= $form->field($model, 'author_ids')->listBox($authorsList, [
        'multiple' => true,
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Изменить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
