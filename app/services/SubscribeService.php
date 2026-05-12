<?php

namespace app\services;

use app\components\SmsSenderInterface;
use app\repositories\SubscribeRepository;
use Yii;
use app\models\Subscriber;
use app\models\Book;

class SubscribeService
{
    public function __construct(
        private SubscribeRepository $repository,
        private ?SmsSenderInterface $smsSender = null,
    )
    {
    }

    /**
     * @param int $authorId
     * @param string|null $phone
     * @return Subscriber
     * @throws \DomainException
     */
    public function subscribe(int $authorId, ?string $phone = null): Subscriber
    {
        if (!Yii::$app->user->isGuest) {
            $phone = Yii::$app->user->identity->phone;
        }
        if ($phone === null) {
            throw new \DomainException("Номер телефона не задан.");
        }
        $subscription = $this->repository->findSubscription($phone, $authorId);
        if ($subscription) {
            throw new \DomainException("Вы уже подписаны на этого автора.");
        }
        return $this->repository->createSubscription($phone, $authorId);
    }
    
    /**
     * @param array $ids
     * @param Book $book
     * @return void
     */
    public function notify(array $ids, Book $book): void
    {
        $subscribers = $this->repository->findSubscribersByAuthors($ids);
        $messages = [];
        foreach ($subscribers as $subscriber) {
            $phone = ltrim($subscriber->phone, '+');
            $messages[] = [
                'to' => $phone,
                'text' => sprintf("Книга '%s' от '%s' уже доступна!", $book->title, $subscriber->author->getFullName()),
            ];
        }

        $this->smsSender()->sendBatch($messages);
    }

    private function smsSender(): SmsSenderInterface
    {
        return $this->smsSender ?? Yii::$app->smsPilot;
    }
}
