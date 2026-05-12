<?php
/* @var $this yii\web\View */
/* @var $model app\models\Book */
/* @var $authors app\models\Author[] */

use yii\helpers\Html;

$this->title = 'Изменить книгу: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->book_id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="book-update">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('_form', ['model' => $model, 'authors' => $authors]) ?>
    
</div>
