<?php
define('APP_ROOT',dirname(__FILE__));
$yii=APP_ROOT.'/../yii-1.1.10.r3566/framework/yii.php';
$config=APP_ROOT.'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);


require_once($yii);
require_once(APP_ROOT.'/protected/extensions/Accessory.php');
// 设置服务器默认时区为UTC
date_default_timezone_set('UTC');
//设定系统出错时招待的错误处理方法
set_error_handler("myErrorHandler");
//如果系统有未捕获的异常时会执行myExceptionHandler方法
set_exception_handler("myExceptionHandler");
Yii::createWebApplication($config)->run();
