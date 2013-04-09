<?php 

require_once(APP_ROOT.'/protected/extensions/SyncAccessory.php');

class SubjectsyncController extends Controller
{
	
	/**
	 * Default response format
	 */
	Const RESPONSE_DATA_FORMAT = 'json';
	
	Const RESPONSE_STATUS_GOOD = '1';
	Const RESPONSE_STATUS_BAD = '2';
	Const SYNC_STATUS_TASK_DONE_BEFORE = '300';
	Const SYNC_STATUS_OBJECT_DELETED = '301';
	Const RESPONSE_STATUS_USER_NOT_EXIST = '801';
	Const RESPONSE_STATUS_DUPLICATED_SUBJECT = '802';
	Const RESPONSE_STATUS_SUBJECT_NOT_EXIST = '803';
	Const RESPONSE_STATUS_DUPLICATED_NOTE = '804';
	Const RESPONSE_STATUS_NOTE_NOT_EXIST = '805';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';

	/**
	 * 创建新记录对象或用户信息实体
	 */
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
				
				Accessory::writeLog('subject found. uuid: ' . $subject->uuid . ' name: '. $subject->display_name);
				
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
			$user = User::model()->findByPk($user_id);
			foreach ($_POST['Subject'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($subject->hasAttribute($key)) {
					switch ($key) {
						// 忽略以下参数
						case 'last_update_time':
						case 'usn':
							break;
							
						case 'uuid':
						case 'current_avatar_uuid':
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
			
			//$createSuccess = true;
			
			$subject = DBTransactionManager::saveObjectWithUSN($subject, $user_id);
			if($subject !== null) {
				// 关联新创建的subject和用户
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
											'Subject sync failed');
			}
		}
	}
	
	/**
	 * 更新用户账户的update_count属性值
	 * @param Subject $subject 与update_count关联的subject实体
	 * @param User $user 用户账户实体
	 * @return Subject|NULL 更新用户账户的update_count属性成功时返回带有合法usn的subject实体，否则返回NULL
	 */
	private function _updateUserUSN($subject, $user)
	{
		try {
			$attributes = array(
					'last_update_time' => date('Y-m-d H:i:s'),
				);
			$user->updateByPk($user->id, $attributes);

			// 没有异常抛出，返回原始的subject实体
			return $subject;
		} catch (StaleObjectError $e) {
			// 更新用户账户的update_count时遭遇存储冲突异常
			// 用当前的update_count值更新subject的usn并再次尝试存储更新
			$newUser = User::model()->findByPk($user->id);
			$subject->usn = $newUser->update_count;
			if ($subject->save())
				$this->_updateUserUSN($subject, $user);
			else {
				$subject->delete();
				return null;
			}
		}
	}
	
	public function actionUpdate()
	{
		Accessory::writeLog('about to update subject');
		
		if(isset($_POST['Subject']))
		{
			$user_id = $_POST['Subject']['user_id'];
			$subject_uuid = Accessory::packUUID($_POST['Subject']['uuid']);
			
			$user = User::model()->findByPk($user_id);
			
			if ($user === null) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST, 
												'user not exist', 
												array('subject/user_id'=>$user_id, 
														'subject/uuid'=>$_POST['Subject']['uuid']),
														__CLASS__ .' '. __FUNCTION__);
		
				Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST, 
										'User not exist, please contact Jugaogao customer service.');
			}

			$subject = Subject::fetchSubject($user_id, $subject_uuid);
			
			if ($subject === null) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
												'update subject not exist', 
												array('subject/uuid'=>$_POST['Subject']['uuid']),
												__CLASS__ .' '. __FUNCTION__);
		
				Accessory::warningResponse(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
			}
			
			//$model->attributes=$_POST['Subject'];
			foreach ($_POST['Subject'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($subject->hasAttribute($key)) {
					switch ($key) {
						case 'uuid':
						case 'create_time':
						case 'usn':
							break;

						case 'current_avatar_uuid':
							$subject->$key = Accessory::packUUID($value);
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
			
			$subject = DBTransactionManager::saveObjectWithUSN($subject, $user_id);
			
			if($subject !== null) {
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

	public function actionDelete()
	{
		Accessory::writeLog('about to delete subject');
	
		$subject_id = $_GET['id'];
		$subject_usn = $_GET['usn'];
		$subject = Subject::model()->findByPk($subject_id);	
		if ($subject === null) {
			Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST,
				'delete subject not exist',
				array('subject/id'=>$_GET['id']),
				__CLASS__ .' '. __FUNCTION__);
		
			Accessory::warningResponse(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST,
				'System error, please contact Jugaogao customer service.');
		}
		if ($subject->deleted == 1) {
			SyncAccessory::deleteSyncTaskDoneBeforeFor($subject);
		} elseif ($subject->usn == $subject_usn) {
			$user = User::fetchOwnerForSubject($subject->id);
			if (DBTransactionManager::deleteObject($subject, $user->id)) {
				$response = array(
						'id' => $subject->id,
						'usn' => $subject->usn,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'sync_status_code' => self::SYNC_STATUS_OBJECT_DELETED,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Subject sync failed');
			}
		} elseif ($subject->usn > $subject_usn) {
			// 服务器端的数据比客户端的数据更新
			// 提示客户端进行增量同步
			Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
							'Delete target usn is newer than device\'s',
							self::SYNC_STATUS_NEED_INCREMENT_SYNC);
		} else {
			Accessory::writeLog('this should not happen!');
			// Errors occurred
			Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Subject sync failed');
		}
	}
	
}