<?php

namespace app\services;

use app\models\User;
use app\repositories\UserRepository;
use Yii;
use yii\web\BadRequestHttpException;

class UserService
{
    public function __construct(private UserRepository $repository)
    {
    }
    
    /**
     * @param array $data
     * @return User
     * @throws \RuntimeException
     */
    public function signup(array $data): User
    {
        $user = new User();
        $user->phone = $data['phone'];
        $user->setPassword($data['password']);
        $user->generateAuthKey();
        if (!$user->save()) {
            throw new \RuntimeException("Ошибка регистрации: " . implode(', ', $user->getFirstErrors()));
        }
        return $user;
    }
    
    /**
     * @param string $phone
     * @param string $password
     * @return User
     * @throws BadRequestHttpException
     */
    public function login(string $phone, string $password): User
    {
        $user = $this->repository->findByPhone($phone);
        if (!$user || !$user->validatePassword($password)) {
            throw new BadRequestHttpException("Неверный номер телефона или пароль.");
        }
        return $user;
    }
}
