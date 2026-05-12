<?php
/* @var $this yii\web\View */
/* @var $model app\models\Book */
/* @var $authors app\models\Author[] */

use yii\helpers\Html;

$this->title = 'Создать книгу';
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-create">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('_form', ['model' => $model, 'authors' => $authors]) ?>
    
</div>
