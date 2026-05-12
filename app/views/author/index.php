<?php
/* @var $this yii\web\View */
/* @var $searchModel app\models\AuthorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Авторы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-index">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <p>
        <?= Html::a('Создать автора', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],
            'author_id',
            'last_name',
            'first_name',
            'patronymic',
            [
                'attribute' => 'books',
                'label'     => 'Книги',
                'format'    => 'raw',
                'value'     => function($model) {
                    return implode(', ', array_map(function($book) {
                        return $book->title;
                    }, $model->books));
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {subscribe}',
                'buttons' => [
                    'subscribe' => function ($url, $model, $key) {
                        return Html::a(
                            '&#128276;',
                            ['subscribe', 'id' => $model->author_id],
                            ['title' => 'Подписаться', 'data-pjax' => '0']
                        );                        
                    },
                ],
            ],
        ],
    ]); ?>

</div>
