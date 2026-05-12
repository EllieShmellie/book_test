<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/test_db.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@covers' => '@app/runtime/test-covers',
        '@coversUrl' => '/images/covers',
    ],
    'language' => 'en-US',
    'components' => [
        'db' => $db,
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
            'messageClass' => 'yii\symfonymailer\Message'
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            // but if you absolutely need it set cookie domain to localhost
            /*
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
        ],
    ],
    'container' => [
        'definitions' => [
            \app\components\SmsSenderInterface::class => static fn () => new class implements \app\components\SmsSenderInterface {
                public function sendBatch(array $messages, array $additionalParams = []): ?array
                {
                    return null;
                }
            },
            \app\repositories\AuthorRepositoryInterface::class => \app\repositories\AuthorRepository::class,
            \app\repositories\BookRepositoryInterface::class => \app\repositories\BookRepository::class,
            \app\repositories\SubscribeRepositoryInterface::class => \app\repositories\SubscribeRepository::class,
        ],
    ],
    'params' => $params,
];
