<?php
class RestController extends Controller
{

	// constants for event validate type
	Const EVENT_VALIDATE_TYPE_REST_PASSWORD = 1;
	
	Const JGG_CUSTOMER_SERVICE_EMAIL_ADDRESS = 'anna@jugaogao.com';
	Const JGG_CUSTOMER_SERVICE_REPRESENTATIVE_NAME = 'Anna';
	Const RESPONSE_STATUS_BAD = '2';
	Const RESPONSE_STATUS_INSUFFICIENT_PARAM = '103';
	Const RESPONSE_STATUS_USER_NOT_EXIST = '801';
	
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
				'actions'=>array('authkey', 'syncstatus'),
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
		$client_id = Accessory::packUUID($_GET['id']);
		
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
			Accessory::writeLog('auth key ' . ': ' . $response['authkey'] . ', ' . Accessory::unpackUUID($actionAuth->key));
			Accessory::sendRESTResponse(201, CJSON::encode($response));
		} else {
			// Errors occurred
			Accessory::warningResponse(RESPONSE_STATUS_BAD, '创建授权令牌失败');
		}
	}
	
	/**
	 * return sync status for specific account
	 */
	public function actionSyncstatus()
	{
		Accessory::writeLog('user id: ' . $_GET['id']);
		
		if(!isset($_GET['id']))
		{
			Accessory::warningResponse(self::RESPONSE_STATUS_INSUFFICIENT_PARAM,
			'Insufficient parameter.');
		} else {
			$user_id = $_GET['id'];
		}
		if (!User::isUserExist($user_id)) {
			Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST,
												'user is not exist',
												array('syncstatus/user_id'=>$user_id));
		
			Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST,
										'系统错误, 请联系举高高客服。');
		}
		// 返回测试数据。未来需要用正式数据代替
		$response = array(
					'update_count' => 0,
					'full_sync_before' => date('Y-m-d H:i:s'),
				);
		Accessory::writeLog('sending response: full_sync_before' . $response["full_sync_before"]);
		Accessory::sendRESTResponse(201, CJSON::encode($response));
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
		
		Accessory::writeLog($put_vars[id] . ' ' . $put_vars[password]);
	
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