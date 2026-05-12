<?php
/* @var $this yii\web\View */
/* @var $model app\models\Book */

use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->book_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->book_id], [
            'class' => 'btn btn-danger',
            'data'  => [
                'confirm' => 'Вы уверены, что хотите удалить эту книгу?',
                'method'  => 'post',
            ],
        ]) ?>
    </p>
    
    <?= DetailView::widget([
        'model'      => $model,
        'attributes' => [
            'book_id',
            'title',
            'year',
            'description:ntext',
            'isbn',
            [
                'attribute' => 'cover',
                'format' => 'html',
                'value' => function($model) {
                    if (!$model->cover) {
                        return 'Нет изображения';
                    }
                    return Html::img(
                        Yii::getAlias('@coversUrl') . '/' . $model->cover, 
                        ['width' => '150', 'alt' => $model->title]
                    );
                },
            ],
            'created_at',
            'updated_at',
            [
                'attribute' => 'authors',
                'label'     => 'Авторы',
                'value'     => function($model) {
                    return implode(', ', ArrayHelper::getColumn($model->authors, 'last_name'));
                },
            ],
        ],
    ]) ?>
    
</div>
