<?php
/* @var $this yii\web\View */
/* @var $model app\models\Author */

use yii\helpers\Html;

$this->title = 'Изменить автора: ' . $model->first_name . ' ' . $model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->first_name . ' ' . $model->last_name, 'url' => ['view', 'id' => $model->author_id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="author-update">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
