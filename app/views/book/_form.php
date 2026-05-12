<?php
/* @var $this yii\web\View */
/* @var $model app\models\Book */
/* @var $authors app\models\Author[] */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

$authorsList = ArrayHelper::map(
    $authors,
    'author_id',
    static fn ($author) => trim($author->getFullName())
);
if (!$model->isNewRecord && empty($model->author_ids)) {
    $model->author_ids = ArrayHelper::getColumn($model->authors, 'author_id');
}

$authorSearchId = Html::getInputId($model, 'author_ids') . '-search';
$authorPickerId = Html::getInputId($model, 'author_ids') . '-picker';
$authorSearchInput = Html::textInput(null, '', [
    'id' => $authorSearchId,
    'class' => 'form-control mb-2',
    'placeholder' => 'Поиск автора',
    'autocomplete' => 'off',
]);

$this->registerCss(<<<CSS
.book-author-picker {
    max-height: 220px;
    overflow-y: auto;
}
.book-author-option {
    padding: 4px 0;
}
CSS);

$this->registerJs('
const authorSearch = document.getElementById(' . Json::htmlEncode($authorSearchId) . ');
const authorOptions = document.querySelectorAll("#' . $authorPickerId . ' .book-author-option");
if (authorSearch) {
    authorSearch.addEventListener("input", () => {
        const query = authorSearch.value.trim().toLocaleLowerCase("ru-RU");
        authorOptions.forEach((option) => {
            option.hidden = query !== "" && !option.dataset.authorSearch.includes(query);
        });
    });
}
');

?>

<div class="book-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'year')->input('number', ['min' => 1, 'max' => 9999]) ?>
    
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
    
    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'cover_file')->fileInput() ?>
    
    <?= $form->field($model, 'author_ids', [
        'template' => "{label}\n{$authorSearchInput}\n{input}\n{error}",
    ])->checkboxList($authorsList, [
        'class' => 'book-author-picker border rounded px-3 py-2',
        'id' => $authorPickerId,
        'item' => static function ($index, $label, $name, $checked, $value) use ($model) {
            $id = Html::getInputId($model, 'author_ids') . '-' . $index;
            $checkbox = Html::checkbox($name, $checked, [
                'value' => $value,
                'id' => $id,
                'class' => 'form-check-input',
            ]);
            $checkboxLabel = Html::label(Html::encode($label), $id, [
                'class' => 'form-check-label ms-1',
            ]);

            return Html::tag('div', $checkbox . $checkboxLabel, [
                'class' => 'form-check book-author-option',
                'data-author-search' => mb_strtolower($label, 'UTF-8'),
            ]);
        },
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Изменить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
