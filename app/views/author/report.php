<?php

use yii\helpers\Html;

/** @var app\models\Author[] $authors */
/** @var int|string $year */

$this->title = "Топ-10 авторов за {$year} год";
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>

<?= Html::beginForm(['report'], 'get', ['class' => 'row gy-2 gx-2 align-items-end mb-3']) ?>
    <div class="col-auto">
        <?= Html::label('Год издания', 'report-year', ['class' => 'form-label']) ?>
        <?= Html::input('number', 'year', $year, [
            'id' => 'report-year',
            'class' => 'form-control',
            'min' => 1,
            'max' => 9999,
        ]) ?>
    </div>
    <div class="col-auto">
        <?= Html::submitButton('Показать', ['class' => 'btn btn-primary']) ?>
    </div>
<?= Html::endForm() ?>

<?php if (empty($authors)): ?>
    <p class="text-muted">За выбранный год данных нет.</p>
<?php else: ?>
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
                    <td><?= Html::encode($author->getFullName()) ?></td>
                    <td><?= (int) $author->booksCount ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
