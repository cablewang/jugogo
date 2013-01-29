<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Jugaogao',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'doudou12',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			//'ipFilters'=>array('127.0.0.1','::1'),
		),
		
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'post/<id:\w+>/<title:.*?>'=>'post/view',
        		'posts/<tag:.*?>'=>'post/index',
        		
        		// REST API URL 每一个pattern只代表一个URL（未来由苏熊抽象route中API URL的通用pattern）
				
				// 申请临时授权令牌
				array('rest/authkey', 'pattern'=>'api/<model:authkey>/<id:\w+>', 'verb'=>'GET'),
				
				// 更新同步状态
				array('rest/syncstatus', 'pattern'=>'api/<model:syncstatus>/<id:\w+>', 'verb'=>'GET'),
				
				// 用户注册
				array('userrest/signup', 'pattern'=>'api/<model:signup>', 'verb'=>'POST'),
				
				// 用户登录
				array('userrest/signin', 'pattern'=>'api/<model:signin>', 'verb'=>'PUT'),
				
				// 密码重置（功能尚未完成）
				array('userrest/resetpassword', 'pattern'=>'api/<model:restpassword>', 'verb'=>'PUT'),
				
				// 下载某一用户的所有记录对象和用户信息
				array('userrest/fetchsubjects', 'pattern'=>'api/<model:fetchsubjects>', 'verb'=>'GET'),
				
				// 以下是各个实体的同步功能，同步功能包括上传创建，下载读取，上传修改，和客户端删除
				
				// subject实体的同步功能
				array('subjectsynch/create', 'pattern'=>'api/subject/', 'verb'=>'POST'),
				
				// avatar实体的同步功能
				array('avatarsynch/create', 'pattern'=>'api/avatar/', 'verb'=>'POST'),
				
				// note实体的同步功能
				array('notesynch/processsynch', 'pattern'=>'api/synch/<model:note>', 'verb'=>'POST'),
				
				// photo实体的同步功能
				array('photosynch/processsynch', 'pattern'=>'api/synch/<model:photo>', 'verb'=>'POST'),
				
				// audio实体的同步功能
				array('audiosynch/processsynch', 'pattern'=>'api/synch/<model:audio>', 'verb'=>'POST'),
				
				// video实体的同步功能（未实现的预留功能）
				array('videosynch/processsynch', 'pattern'=>'api/synch/<model:video>', 'verb'=>'POST'),
				
				// weather实体的同步功能（因为第三方服务器连接不稳定，在第一个版本中暂时被停用）
				array('weathersynch/processsynch', 'pattern'=>'api/synch/<model:weather>', 'verb'=>'POST'),
				
				// location实体的同步功能（因为地理信息功能仍有Buy并且功能不全，在第一版中被暂时停用）
				array('locationsynch/processsynch', 'pattern'=>'api/synch/<model:location>', 'verb'=>'POST'),
				
				// testing rule
				array('userrest/optimistLocking', 'pattern'=>'api/<model:optimistLocking>/<id:\w+>', 'verb'=>'GET'),
				
				// Yii的默认规则。
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
			//'showScriptName'=>false,
		),
		
		'db'=>array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=vjugogo',
			'emulatePrepare' => true,
			'username' => 'vjugogo',
			'password' => 'vjgg!@#qwe',
			'charset' => 'utf8',
		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'kaibo.wang@gmail.com',
	),
);