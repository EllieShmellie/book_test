<?php

namespace app\models;

use yii\base\Model;
use borales\extensions\phoneInput\PhoneInputValidator;

class SignupForm extends Model
{
    public $phone;
    public $password;
    public $password_repeat;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['phone', 'password', 'password_repeat'], 'required'],
            [['phone'], PhoneInputValidator::class, 'region' => ['RU']],
            ['phone', 'match', 'pattern' => '/^\+?\d{10,15}$/', 'message' => 'Введите корректный номер телефона.'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают.'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'phone'           => 'Номер телефона',
            'password'        => 'Пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }
}
