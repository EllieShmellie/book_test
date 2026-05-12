<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . (getenv('TEST_DB_HOST') ?: 'db')
        . ';dbname=' . (getenv('TEST_DB_NAME') ?: 'book_test_test'),
    'username' => getenv('TEST_DB_USER') ?: 'root',
    'password' => getenv('TEST_DB_PASSWORD') ?: 'secret',
    'charset' => 'utf8',
];
