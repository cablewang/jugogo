<?php 

// 用户账户相关功能REST API
// 包含：用户注册，用户登录
class UserrestController extends Controller
{
	
	/**
	 * Default response format
	 */
	Const RESPONSE_DATA_FORMAT = 'json';
	
	Const RESPONSE_STATUS_GOOD = '1';
	Const RESPONSE_STATUS_BAD = '2';
	Const RESPONSE_STATUS_BAD_SIGNUP = '901';
	Const RESPONSE_STATUS_EMAIL_IS_TAKEN = '902';
	Const RESPONSE_STATUS_BAD_SIGNIN = '903';
	Const RESPONSE_STATUS_INSUFFICIENT_PARAM = '103';
	Const RESPONSE_STATUS_NO_ACCESS_RIGHT = '401';

	/**
	 * 用户注册
	 * 过程中将检测客户端发来的临时信任状
	 * 
	 */
	public function actionSignup()
	{
	
		// 如果post中没有参数，发送响应“缺少参数”错误信息后终结当前脚本
		if (empty($_POST['User'])) {
			Accessory::warningResponse(self::RESPONSE_STATUS_BAD_SIGNUP, 'Lack of parameter');
		}
		
		// 分解post 'form'内容并创建user实体
		$user = new User;
		
		// 创建用户实体
		foreach ($_POST['User'] as $key=>$value) {
			
			// debug
			Accessory::writeLog($key . ' ' . $value);
			
			if ($user->hasAttribute($key)) {
				switch ($key) {
					case 'email':
						// 校验email或手机号码是否已经被占用
						if (User::model()->findByAttributes(array('email'=>$value))) {
							Accessory::warningResponse(self::RESPONSE_STATUS_EMAIL_IS_TAKEN, 'The Email address has already been taken. Forgot password?');
						}
						$user->$key = $value;
						break;
						
					case 'password': 
						// 密码格式验证
						if (strlen($value) < 6 || strlen($value) > 12) {
							Accessory::warningResponse(self::RESPONSE_STATUS_BAD_SIGNUP, 'Password invalide');
						}
						$user->$key = md5($value);
						break;
						
					default:
						$user->$key = $value;
							
				} 
			} else {
				if ($key = 'uuid') {
					$firstHalf = substr($value, 0, 16);
					$baseUUID = Accessory::packUUID($value);
				} else {
					// 如果发现不可识别的参数，报错
					Accessory::warningResponse(self::RESPONSE_STATUS_BAD_SIGNUP, 'Parameter ' . $key . ' is not allowed');
				}
			}
		}

		$testKey = $_POST['addition_info'];
		//debug
		Accessory::writeLog('addition_info   ' . $testKey);
	
		// 校验临时信任状
		if ($testKey == '') {
			Accessory::warningResponse(self::RESPONSE_STATUS_NO_ACCESS_RIGHT,
					'You are not authorized to perform this action');
		} else {
			$actionAuth = Actionauth::model()->findByAttributes(array('client_sub_id'=>$baseUUID));
			
			if (!empty($actionAuth)){
				$authKey = '';
				$key = Accessory::unpackUUID($actionAuth->key);
				$interString = substr_replace($key, $firstHalf, 0, 16);
				Accessory::writeLog($interString);
				$authKey = md5($interString);
				Accessory::writeLog($authKey . ' ' . $testKey . ' ' . $interString . ' ' . $firstHalf);
				if ($testKey != $authKey) {
					Accessory::warningResponse(self::RESPONSE_STATUS_NO_ACCESS_RIGHT, 'You are not authorized to perform this action');
				}
			} else {
				Accessory::warningResponse(self::RESPONSE_STATUS_NO_ACCESS_RIGHT, 'You are not authorized to perform this action');
			}
		}
	
		// 初始化用户账户
		$user->state = 1;	// 账户状态正常
		date_default_timezone_set("UTC");
		$user->reg_time = date('Y-m-d H:i:s');	// 注册时间（有可能与创建时间不同）
		$user->last_login_time = $user->reg_time;
		$user->score = 0;
		$user->update_count = 0;
		$user->full_sync_before = $user->reg_time;
		
		// 持久化user实体
		if ($user->save()) {
			
			// 完成注册后删除临时信任状
			$actionAuth->delete();
			
			// debug
			Accessory::writeLog('new user created');
				
			// 发送注册成功响应信息
			$response = array(
					'id' => $user->id,
					'password' => $user->password,
					'state' => (string)$user->state,
					'create_time' => date('Y-m-d H:i:s'),
					'status_code' => self::RESPONSE_STATUS_GOOD,
					'message' => '',
			);
			Accessory::sendRESTResponse(201, CJSON::encode($response));
		} else {
			// 持久化user实体失败
			Accessory::warningResponse(self::RESPONSE_STATUS_BAD_SIGNUP, 'Could not create new user');
		}
		$log->lclose();
	}
	
	/**
	 * testing for data locking
	 */
	public function actionOptimistLocking()
	{
		Accessory::writeLog($_GET['id']);
		$user1 = User::model()->findByPk($_GET['id']);
		$user2 = User::model()->findByPk($_GET['id']);
		$user3 = User::model()->findByPk($_GET['id']);
		$user4 = User::model()->findByPk($_GET['id']);
		
		$seconds = 1;
		
		$attributes = array(
				'last_update_time' => date('Y-m-d H:i:s'),
				);
		try {
			$user1->updateByPk($_GET['id'], $attributes);
		} catch (StaleObjectError $e) {
			Accessory::writeLog($e->getMessage());
		}
		
		sleep($seconds);
		$attributes = array(
				'last_update_time' => date('Y-m-d H:i:s'),
		);
		try {
			$user2->updateByPk($_GET['id'], $attributes);
		} catch (StaleObjectError $e) {
			Accessory::writeLog($e->getMessage());
		}

		sleep($seconds);
		$attributes = array(
				'last_update_time' => date('Y-m-d H:i:s'),
		);
		try {
			$user3->updateByPk($_GET['id'], $attributes);
		} catch (StaleObjectError $e) {
			Accessory::writeLog($e->getMessage());
		}

		sleep($seconds);
		$attributes = array(
				'last_update_time' => date('Y-m-d H:i:s'),
		);
		try {
			$user4->updateByPk($_GET['id'], $attributes);
		} catch (StaleObjectError $e) {
			Accessory::writeLog($e->getMessage());
		}
	}
	
	/**
	 * receives request from client and sends back an authentication key
	 */
	public function actionResetpassword()
	{
		$log = new Logging();
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		// fetch and decode the PUT parameters
		$json = file_get_contents('php://input');
		$put_vars = CJSON::decode($json, true);
	
		$log->lwrite($put_vars['id'] . ' ' . $put_vars['password']);
	
		$user = User::model()->findByPk($put_vars["id"]);
		$user->password = $put_vars["password"];
	
		if ($user->save()) {
			$response = array(
					'status_code' => self::RESPONSE_STATUS_GOOD,
					'error_message' => '',
			);
			$log->lwrite("ok");
			Accessory::sendRESTResponse(201, CJSON::encode($response));
		} else {
			$log->lwrite("not ok");
			Accessory::warningResponse(self::RESPONSE_STATUS_BAD, '');
		}
	}
	
	public function actionSignin()
	{
		$log = new Logging();
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		// fetch and decode the PUT parameters
		$json = file_get_contents('php://input');
		$put_vars = CJSON::decode($json, true);
		
		$indentity =new UserIdentity($put_vars['email'], $put_vars['password']);
		if(!$indentity->authenticate()) {
			// Errors occurred
			$response = array(
					'status_code' => self::RESPONSE_STATUS_BAD_SIGNIN,
					'error_message' => 'Incorrect username or password',
			);
			$log->lwrite("not ok");
			Accessory::sendResponse(500, CJSON::encode($response));
			//return;
			//$this->addError('password','Incorrect username or password.');
		} else {
			$user = User::model()->findByAttributes(array('email'=>$put_vars['email']));
			$response = array(
					"id" => $user->id,
					"password" => $user->password,
					"state" => (string)$user->state,
					"create_time" => date('Y-m-d H:i:s'),
					'status_code' => self::RESPONSE_STATUS_GOOD,
					'error_message' => '',
			);
			$log->lwrite("ok");
			Accessory::sendResponse(201, CJSON::encode($response));
			//return;
		}
	}
	
	// after user signin, App request for all profiles on user related subjects
	// return a set of subjects
	public function actionFetchsubjects()
	{
		$log = new Logging();
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		if($_GET['user_id'])
		{
			$subject_set;
			//$user = User::model()->findByPk($_GET['user_id']);
			$subjects = Subject::fetchAllSubjects($_GET['user_id']);
			if (count($subjects) > 0) {
				Accessory::sendRESTResponse(201, CJSON::encode($subjects));
				//return;
			}
		} else {
			Accessory::warningResponse(self::RESPONSE_STATUS_INSUFFICIENT_PARAM,
							'Insufficient parameter.');
			//return;
		}
	}
	
}