<?php

use yii\helpers\Html;

/** @var app\models\Author[] $authors */
/** @var int|string $year */

$this->title = "Топ-10 авторов за {$year} год";
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>

<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Автор</th>
            <th>Количество книг</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($authors as $index => $author): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= Html::encode($author->first_name . ' ' . $author->last_name) ?></td>
                <td><?= count($author->books) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
