<?php 
require_once('Logging.php');
require_once('Accessory.php');

class SubjectsynchController extends Controller
{
	
	/**
	 * Default response format
	 */
	Const RESPONSE_DATA_FORMAT = 'json';
	
	Const RESPONSE_STATUS_GOOD = '1';
	Const RESPONSE_STATUS_BAD = '2';
	Const SYNC_STATUS_TASK_DONE_BEFORE = '300';
	Const RESPONSE_STATUS_USER_NOT_EXIST = '801';
	Const RESPONSE_STATUS_DUPLICATED_SUBJECT = '802';
	Const RESPONSE_STATUS_SUBJECT_NOT_EXIST = '803';
	Const RESPONSE_STATUS_DUPLICATED_NOTE = '804';
	Const RESPONSE_STATUS_NOTE_NOT_EXIST = '805';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	Const JGG_LOG_FILE_PATH = 'myphplog.txt'; 

	/**
	 * receives request from client and sends back an authentication key
	 */
	public function actionProcesssynch()
	{
		switch ($_POST['change_type'])
		{
			case 'create': 
				$this->_createSubject();
				break;
			case 'update':
				$this->_updateSubject();
				break;
			case 'delete':
				$this->_deleteSubject();
				break;
			default:
				;
		}
	}
	
	private function _createSubject()
	{
		$log = new Logging();
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		if(isset($_POST['Subject']))
		{
			$user_id = $_POST['Subject']['user_id'];
			$subject_uuid = pack('h*', $_POST['Subject']['uuid']);
			if (!User::isUserExist($user_id)) {
				
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST, 
												'user is not exist', 
												array('subject/user_id'=>$user_id, 
														'subject/uuid'=>$_POST['Subject']['uuid']));
				
				Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
				//return;
			}
			 
			$subject = Subject::fetchSubject($user_id, $subject_uuid);
			if ($subject !== NULL) {
				
				$log->lwrite('subject found. uuid: ' . $subject->uuid . ' name: '. $subject->display_name .' subject: ' . $subject);
				
				if (strtotime($subject->create_time) !== strtotime($_POST['Subject']['create_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_SUBJECT, 
													'duplicated subject', 
													array('subject/uuid'=>$_POST['Subject']['uuid']));
					
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_SUBJECT, 
												'System error, please contact Jugaogao customer service.');
					//return;
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							"id" => $subject->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
					//return;
				}
			} else {
				$subject = new Subject;
			}
			
			$user_role = 0;
			foreach ($_POST['Subject'] as $key => $value) {
				$log->lwrite($key . ' ' . $value);
				if ($subject->hasAttribute($key)) {
					switch ($key) {
						case 'last_update_time':
							break;
						case 'uuid':
							$subject->$key = pack('h*', $value);
							break;
						default:
							$subject->$key = $value;		
					}
								
				} else {
					switch ($key) {
						case 'role':
							$user_role = $value;
							break;
						case 'user_id':
							break;
						default:
							$log->lwrite($key . ' ' . $value);
							
							Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
																		'Parameter is not allowed.');
					}
				}
			}
			
			if($subject->save()) {
				//$user = Yii::app()->user;
				$user = User::model()->findByPk($user_id);
				$subject->associateUserToSubject($user, $user_role);
				
				$response = array('id' => $subject->id, 
						'status_code' => self::RESPONSE_STATUS_GOOD, 
						'error_message' => '');
				Accessory::sendRESTResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD, 
											'Subject synch failed');
				return;
			}
		}
	}
	
	private function _updateSubject()
	{
		$log = new Logging();
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		$log->lwrite('update subject');
		
		if(isset($_POST['Subject']))
		{
			$user_id = $_POST['Subject']['user_id'];
			$subject_uuid = pack('h*', $_POST['Subject']['uuid']);
			//$log->lwrite('user id: ' . $user_id . ', ' . 'uuid: ' . $subject_uuid);
			if (!User::isUserExist($user_id)) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST, 
												'user not exist', 
												array('subject/user_id'=>$user_id, 
														'subject/uuid'=>$_POST['Subject']['uuid']),
														__CLASS__ .' '. __FUNCTION__);
		
				Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
				return;
			}
				
			if (!(Subject::isSubjectExist($user_id, $subject_uuid))) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
												'update subject not exist', 
												array('subject/uuid'=>$_POST['Subject']['uuid']),
												__CLASS__ .' '. __FUNCTION__);
		
				Accessory::warningResponse(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
				return;
			}
			
			$subject = Subject::fetchSubject($user_id, $subject_uuid);
			
			//$model->attributes=$_POST['Subject'];
			foreach ($_POST['Subject'] as $key => $value) {
				$log->lwrite($key . ' ' . $value);
				if ($subject->hasAttribute($key)) {
					switch ($key) {
						case 'uuid':
						case 'create_time':
							break;
						default:
							$subject->$key = $value;
					}
			
				} else {
					switch ($key) {
						case 'role':
						case 'user_id':
							break;
						default:
							$log->lwrite($key . ' ' . $value);
							Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
															'Parameter is not allowed.');
					}
				}
			}
			
			if($subject->save()) {
				$response = array(
						"id" => $subject->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD, 
										'Subject synch failed');
				return;
			}
		}
	}
	
	public function downloadall()
	{
		
	}
	
}