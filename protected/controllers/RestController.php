<?php

require_once('Logging.php');
require_once('Accessory.php');

class RestController extends Controller
{

	// constants for event validate type
	Const EVENT_VALIDATE_TYPE_REST_PASSWORD = 1;
	
	Const JGG_CUSTOMER_SERVICE_EMAIL_ADDRESS = 'anna@jugaogao.com';
	Const JGG_CUSTOMER_SERVICE_REPRESENTATIVE_NAME = 'Anna';
	Const RESPONSE_STATUS_BAD = '2';
	Const JGG_LOG_FILE_PATH = 'myphplog.txt'; 
	
	/**
	 * Default response format
	 * either 'json' or 'xml'
	 */
	private $format = 'json';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
		array('allow',  // allow all users to perform 'authkey' actions
				'actions'=>array('authkey'),
				'users'=>array('*'),
		),
		array('deny',  // deny all users
				'users'=>array('*'),
		),
		);
	}
	
	/**
	 * receives request from client and sends back an authentication key
	 */
	public function actionAuthkey()
	{
		$log = new Logging();
		$client_id = Accessory::packUUID($_GET['id']);
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		$actionAuth = new Actionauth;
		$actionAuth->client_sub_id = $client_id;
		$phpuuid = Accessory::gen_uuid();
		$actionAuth->key = Accessory::packUUID($phpuuid);
		date_default_timezone_set("UTC");
		$actionAuth->create_time = date('Y-m-d H:i:s');
		
		if ($actionAuth->save()) {
			$response = array(
				'authkey' => $phpuuid,
			);
			$log->lwrite('auth key ' . ': ' . $response['authkey'] . ', ' . Accessory::unpackUUID($actionAuth->key));
			Accessory::sendRESTResponse(201, CJSON::encode($response));
		} else {
			// Errors occurred
			Accessory::warningResponse(RESPONSE_STATUS_BAD, 'can\'t create action authentication');
		}
	}
	
	/**
	 * receives request from client and sends back an authentication key
	 */
	public function actionRestpassword()
	{
		$log = new Logging();
		
		// fetch and decode the PUT parameters
		$json = file_get_contents('php://input');
		$put_vars = CJSON::decode($json, true);
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		$log->lwrite($put_vars[id] . ' ' . $put_vars[password]);
	
		$user_id = $put_vars["id"];
		$validate = new Eventvalidate;
		$validate->user_id = $user_id;
		$validate->type = EVENT_VALIDATE_TYPE_REST_PASSWORD;
		$phpuuid = Accessory::gen_uuid();
		$validate->uuid = pack('h*', $phpuuid);
		date_default_timezone_set("UTC");
		$validate->create_time = date('Y-m-d H:i:s');
	
		if ($validate->save()) {
			$user = User::model()->findByPk($user_id);
			
			Accessory::sendEmail($user->email, "test", JGG_CUSTOMER_SERVICE_EMAIL_ADDRESS, 
					JGG_CUSTOMER_SERVICE_REPRESENTATIVE_NAME, 'test', 'test');
			$response = array(
					'result_code' => 1,
			);
			Accessory::sendRESTResponse(201, CJSON::encode($response));
		} else {
			// Errors occurred
			Accessory::warningResponse(RESPONSE_STATUS_BAD, 'Reset password failed');
		}
	}
	
}