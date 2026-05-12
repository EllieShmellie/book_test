<?php
/* @var $this yii\web\View */
/* @var $model app\models\Author */

use yii\widgets\DetailView;
use yii\helpers\Html;

$this->title = $model->first_name . ' ' . $model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <p>
         <?= Html::a('Изменить', ['update', 'id' => $model->author_id], ['class' => 'btn btn-primary']) ?>
         <?= Html::a('Удалить', ['delete', 'id' => $model->author_id], [
            'class' => 'btn btn-danger',
            'data'  => [
                'confirm' => 'Вы уверены, что хотите удалить этого автора?',
                'method'  => 'post',
            ],
         ]) ?>
         <?= Html::a('Подписаться на обновления', ['author/subscribe', 'id' => $model->author_id], [
        'class' => 'btn btn-success',
    ]) ?>
    </p>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'author_id',
            'last_name',
            'first_name',
            'patronymic',
            'created_at',
            'updated_at',
            [
                'attribute' => 'books',
                'label'     => 'Книги',
                'value'     => function($model) {
                    return implode(', ', array_map(function($book) {
                        return $book->title;
                    }, $model->books));
                },
            ],
        ],
    ]) ?>

</div>
