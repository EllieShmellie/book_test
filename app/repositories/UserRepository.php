<?php

namespace app\repositories;

use app\models\User;
use yii\web\NotFoundHttpException;

class UserRepository
{
    /**
     * @param int $id
     * @return User
     * @throws NotFoundHttpException
     */
    public function findById(int $id): User
    {
        if (($user = User::findOne($id)) !== null) {
            return $user;
        }
        throw new NotFoundHttpException("Пользователь не найден.");
    }
    
    /**
     * @param string $phone
     * @return User|null
     */
    public function findByPhone(string $phone): ?User
    {
        return User::findOne(['phone' => $phone]);
    }
}
