<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int    $author_id
 * @property string $last_name
 * @property string $first_name
 * @property string $patronymic
 * @property string $created_at
 * @property string $updated_at
 * @property int[]  $book_ids
 *
 * @property AuthorBook[] $authorBooks
 * @property Book[] $books
 */
class Author extends ActiveRecord
{
    public int $booksCount = 0;

    /**
     * @var int[]
     */
    public $book_ids = [];

    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return '{{%author}}';
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['last_name', 'first_name'], 'required'],
            [['last_name', 'first_name', 'patronymic'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
            ['book_ids', 'each', 'rule' => [
                'exist',
                'skipOnError' => true,
                'targetClass' => Book::class,
                'targetAttribute' => 'book_id',
            ]],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels(): array
    {
        return [
            'author_id'  => 'ID Автора',
            'last_name'  => 'Фамилия',
            'first_name' => 'Имя',
            'patronymic' => 'Отчество',
            'updated_at' => 'Обновлен',
            'created_at' => 'Создан',
        ];
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return "{$this->last_name} {$this->first_name} {$this->patronymic}";
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthorBooks(): ActiveQuery
    {
        return $this->hasMany(AuthorBook::class, ['author_id' => 'author_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBooks(): ActiveQuery
    {
        return $this->hasMany(Book::class, ['book_id' => 'book_id'])->via('authorBooks');
    }
}
