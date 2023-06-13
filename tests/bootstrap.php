<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', __DIR__.'/../../');

$_SERVER['SCRIPT_NAME']     = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once __DIR__ .  '/../vendor/autoload.php';
require_once __DIR__ .  '/../vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@runtime', __DIR__ . '/_output');

