<?php
define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ .'/../vendor/autoload.php';

foreach ([__DIR__ . '/../runtime', __DIR__ . '/../web/assets'] as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}
