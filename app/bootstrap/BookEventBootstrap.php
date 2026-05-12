<?php

namespace app\bootstrap;

use app\models\Book;
use app\services\SubscribeService;
use Yii;
use yii\base\BootstrapInterface;

class BookEventBootstrap implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        \yii\base\Event::on(
            Book::class,
            Book::EVENT_AFTER_CREATE,
            function (\app\events\BookEvent $event) {
                try {
                    Yii::$container->get(SubscribeService::class)->handleBookCreated($event);
                } catch (\Throwable $e) {
                    Yii::warning($e->getMessage(), __METHOD__);
                }
            },
        );
    }
}
