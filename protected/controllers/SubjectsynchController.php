<?php 
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
	
	public function actionCreate()
	{
		
		if(isset($_POST['Subject']))
		{
			$user_id = $_POST['Subject']['user_id'];
			$subject_uuid = Accessory::packUUID($_POST['Subject']['uuid']);
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
				
				Accessory::writeLog('subject found. uuid: ' . $subject->uuid . ' name: '. $subject->display_name .' subject: ' . $subject);
				
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
				Accessory::writeLog($key . ' ' . $value);
				if ($subject->hasAttribute($key)) {
					switch ($key) {
						// 忽略以下参数
						case 'last_update_time':
							break;
							
						case 'usn':
							// 以下操作前应该锁死当前账号的update_count字段
							$subject->$key = $user->update_count + 1;
							break;
							
						case 'uuid':
							$subject->$key = Accessory::packUUID($value);
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
							Accessory::writeLog($key . ' ' . $value);
							
							Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
																		'Parameter '. $key .' is not allowed.');
					}
				}
			}
			
			Accessory::writeLog('finished all attributes assignment');
			
			if($subject->save()) {
				//$user = Yii::app()->user;
				$user = User::model()->findByPk($user_id);
				
				// subject 实体被成功存储到数据库中
				$user->update_count = $subject->usn;
				$user->save();
				// 应该在这里对当前账户的update_count字段解锁
				
				$subject->associateUserToSubject($user, $user_role);
				
				$response = array(
						'id' => $subject->id, 
						'usn' => $subject->usn,
						'status_code' => self::RESPONSE_STATUS_GOOD, 
						'error_message' => '',
						);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				Accessory::writeLog('subject save failed');
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD, 
											'Subject synch failed');
			}
		}
	}
	
	private function _updateSubject()
	{
		Accessory::writeLog('update subject');
		
		if(isset($_POST['Subject']))
		{
			$user_id = $_POST['Subject']['user_id'];
			$subject_uuid = Accessory::packUUID($_POST['Subject']['uuid']);
			//$log->lwrite('user id: ' . $user_id . ', ' . 'uuid: ' . $subject_uuid);
			if (!User::isUserExist($user_id)) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST, 
												'user not exist', 
												array('subject/user_id'=>$user_id, 
														'subject/uuid'=>$_POST['Subject']['uuid']),
														__CLASS__ .' '. __FUNCTION__);
		
				Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST, 
										'User not exist, please contact Jugaogao customer service.');
			}
			$user = User::model()->findByPk($user_id);	
			
			if (!(Subject::isSubjectExist($user_id, $subject_uuid))) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
												'update subject not exist', 
												array('subject/uuid'=>$_POST['Subject']['uuid']),
												__CLASS__ .' '. __FUNCTION__);
		
				Accessory::warningResponse(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
			}
			
			$subject = Subject::fetchSubject($user_id, $subject_uuid);
			
			//$model->attributes=$_POST['Subject'];
			foreach ($_POST['Subject'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($subject->hasAttribute($key)) {
					switch ($key) {
						case 'uuid':
						case 'create_time':
							break;
							
						case 'usn':
							// 以下操作前应该锁死当前账号的update_count字段
							$subject->$key = $user->update_count + 1;
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
							Accessory::writeLog($key . ' ' . $value);
							Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
															'Parameter is not allowed.');
					}
				}
			}
			
			if($subject->save()) {
				// subject 实体被成功存储到数据库中
				$user->update_count = $subject->usn;
				$user->save();
				// 应该在这里对当前账户的update_count字段解锁
				
				$response = array(
						'id' => $subject->id,
						'usn' => $subject->usn,
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