<?php

namespace app\repositories;

use app\models\Subscriber;

interface SubscribeRepositoryInterface
{
    public function findSubscription(string $phone, int $authorId): ?Subscriber;

    public function createSubscription(string $phone, int $authorId): Subscriber;

    /** @return Subscriber[] */
    public function findSubscribersByAuthors(array $authorIds): array;
}
