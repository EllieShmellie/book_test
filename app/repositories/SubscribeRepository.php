<?php

namespace app\repositories;

use app\models\Subscriber;

class SubscribeRepository
{
    /**
     * @param string $phone
     * @param int $authorId
     * @return Subscriber|null
     */
    public function findSubscription(string $phone, int $authorId): ?Subscriber
    {
        return Subscriber::findOne(['phone' => $phone, 'author_id' => $authorId]);
    }

    /**
     * @param string $phone
     * @param int $authorId
     * @return Subscriber
     * @throws \RuntimeException
     */
    public function createSubscription(string $phone, int $authorId): Subscriber
    {
        $subscription = new Subscriber();
        $subscription->phone = $phone;
        $subscription->author_id = $authorId;
        if (!$subscription->save()) {
            throw new \RuntimeException("Ошибка при создании подписки: " . implode(', ', $subscription->getFirstErrors()));
        }
        return $subscription;
    }

    /**
     * @param int[] $authorIds
     * @return array|Subscriber[]
     */
    public function findSubscribersByAuthors(array $authorIds): array
    {
        return Subscriber::find()->where(['author_id' => $authorIds])->with('author')->all();
    }
}
