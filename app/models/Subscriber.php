<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use borales\extensions\phoneInput\PhoneInputValidator;

/**
 * @property int $subscriber_id
 * @property string $phone
 * @property int $author_id
 *
 * @property Author $author
 */
class Subscriber extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%subscriber}}';
    }

    public function rules(): array
    {
        return [
            [['phone', 'author_id'], 'required'],
            [['author_id'], 'integer'],
            [['phone'], PhoneInputValidator::class, 'region' => ['RU']],
            [['phone', 'author_id'], 'unique', 'targetAttribute' => ['phone', 'author_id']]
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'subscriber_id' => 'ID подписчика',
            'phone'         => 'Телефон',
            'author_id'     => 'Автор',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(Author::class, ['author_id' => 'author_id']);
    }
}
