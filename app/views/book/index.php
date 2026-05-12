<?php
/* @var $this yii\web\View */
/* @var $searchModel app\models\BookSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Книги';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php if (!Yii::$app->user->isGuest): ?>
        <p>
            <?= Html::a('Создать книгу', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'emptyText'    => 'Книги не найдены.',
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],
            'book_id',
            [
                'attribute' => 'cover',
                'format'    => 'html',
                'value'     => static function ($model) {
                    if (!$model->cover) {
                        return null;
                    }

                    return Html::img(
                        Yii::getAlias('@coversUrl') . '/' . $model->cover,
                        [
                            'alt' => $model->title,
                            'style' => 'max-width: 64px; max-height: 88px;',
                        ]
                    );
                },
                'filter'    => false,
            ],
            'title',
            'year',
            'isbn',
            [
                'attribute' => 'author_ids',
                'label'     => 'Авторы',
                'value'     => function ($model) {
                    return implode(', ', array_map(
                        static fn ($author) => $author->getFullName(),
                        $model->authors
                    ));
                },
                'filter'    => false,
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'visibleButtons' => [
                    'update' => !Yii::$app->user->isGuest,
                    'delete' => !Yii::$app->user->isGuest,
                ],
            ],
        ],
    ]); ?>
    
</div>
