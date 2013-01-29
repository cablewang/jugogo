<?php 
class AvatarsynchController extends Controller
{
	
	/**
	 * Default response format
	 */
	Const RESPONSE_DATA_FORMAT = 'json';
	
	Const RESPONSE_STATUS_GOOD = '1';
	Const RESPONSE_STATUS_BAD = '2';
	Const SYNC_STATUS_TASK_DONE_BEFORE = '300';
	Const RESPONSE_STATUS_USER_NOT_EXIST = '801';
	Const RESPONSE_STATUS_SUBJECT_NOT_EXIST = '803';
	Const RESPONSE_STATUS_DUPLICATED_AVATAR = '816';
	Const RESPONSE_STATUS_AVATAR_NOT_EXIST = '817';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	Const JGG_PROFILE_PATH_POSTFIX = 'profile';
	Const JGG_USER_PATH_PREFIX = 'private';

	/**
	 * 创建新头像实体
	 */
	public function actionCreate()
	{
		
		if(isset($_POST['Avatar']))
		{
			$user_id = $_POST['Avatar']['user_id'];
			$subject_uuid = Accessory::packUUID($_POST['Avatar']['owner_uuid']);
			
			Accessory::writeLog('subject uuid: ' . $subject_uuid);
			
			$avatar_uuid = Accessory::packUUID($_POST['Avatar']['uuid']);
			$subject_id = Subject::fetchSubjectId($user_id, $subject_uuid);
			if ($subject_id == NULL) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
												'Note is not exist', 
												array('avatar/subject_uuid'=>$subject_uuid, 
														'avatar/uuid'=>$_POST['Avatar']['uuid']));
		
				Accessory::warningResponse(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
				
			}

			// 加载User实体，稍后获取USN时使用
			$user = User::model()->findByPk($user_id);
			
			$avatar = Avatar::model()->findByAttributes(array('subject_id'=>$subject_id, 'uuid'=>$avatar_uuid));
			if ($avatar !== NULL) {
				if (strtotime($avatar->create_time) !== strtotime($_POST['Avatar']['create_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_AVATAR,
														'Duplicated Avatar',
														array('avatar/subject_id'=>$subject_id,
														'avatar/uuid'=>$_POST['Avatar']['uuid']));
			
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_AVATAR,
												'System error, please contact Jugaogao customer service.');
				
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							"id" => $avatar->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
					
				}
			} else {
				$avatar = new Avatar;
			}
			
			if (isset($_FILES)) {
				$filePath = $this->_targetFilePath($user_id, $_POST['Avatar']['owner_uuid']);
				if (!file_exists($filePath)) {
					mkdir($filePath, 0755, true);
				}
				foreach ($_FILES as $file) {
					$fileDestination = $filePath . '/' . $file['name'];
					if (!move_uploaded_file($file['tmp_name'], $fileDestination)) {
						// Errors occurred
						$response = array(
										'status_code' => self::RESPONSE_STATUS_BAD,
										'error_message' => 'File upload failure',
									);
						Accessory::warningResponse(self::RESPONSE_STATUS_FILE_UPLOAD_FAILED, 
													'File upload failure');
						
					}
				}
			}
				
			foreach ($_POST['Avatar'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($avatar->hasAttribute($key)) {
					switch ($key) {
						
						case 'last_update_time':
							break;
							
						case 'uuid':
							$avatar->$key = Accessory::packUUID($value);
							break;

						case 'usn':
								// 以下操作前应该锁死当前账号的update_count字段
								$avatar->$key = $user->update_count + 1;
								break;
						default:
							$avatar->$key = $value;
					}
		
				} else {
					switch ($key) {
						case 'user_id':
						case 'server_id':
							break;
						
						case 'owner_uuid':
							$avatar->subject_id = $subject_id;
							break;
				
						default:
							Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
							'Parameter is not allowed.');
					}
					
				}
			}
			Accessory::writeLog('uuid: ' . $avatar->uuid . 
							' subject id: ' . $avatar->subject_id .
							' name: '. $avatar->avatar_name . 
							' thumb name: ' . $avatar->avatar_thumb_name .
							' create time: ' . $avatar->create_time );
			//print_r($avatar);	
			if($avatar->save()) {
				$response = array(
						"id" => $avatar->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
				
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD, 
											'Avatar synch failed');
				
			}
		}
	}
	
	private function _updateAvatar()
	{
		
	}
	
	private function _deleteAvatar()
	{
		
	}
	
	
	private function _targetFilePath($user_id, $subject_uuid)
	{
		return self::JGG_USER_PATH_PREFIX . '/' .
				$user_id . '/' .
				$subject_uuid . '/' .
				self::JGG_PROFILE_PATH_POSTFIX;
	}
}
	