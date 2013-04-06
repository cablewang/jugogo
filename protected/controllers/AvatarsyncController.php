<?php 

require_once(APP_ROOT.'/fastdfs/fastDFS.php');

class AvatarsyncController extends Controller
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
			$subject_id = $_POST['Avatar']['subject_id'];
			
			Accessory::writeLog('subject id: ' . $subject_id);
			
			$avatar_uuid = Accessory::packUUID($_POST['Avatar']['uuid']);
			//$subject_id = Subject::fetchSubjectId($user_id, $subject_uuid);
			if (!Subject::isSubjectExist($subject_id)) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
												'Note is not exist', 
												array('avatar/subject_uuid'=>$subject_uuid, 
														'avatar/uuid'=>$_POST['Avatar']['uuid']));
		
				Accessory::warningResponse(self::RESPONSE_STATUS_SUBJECT_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
				
			}
			
			if (Avatar::isAvatarExist($subject_id, $avatar_uuid)) {
				if (strtotime($avatar->create_time) !== strtotime($_POST['Avatar']['create_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_AVATAR,
														'Duplicated Avatar',
														array('avatar/subject_id'=>$subject_id,
														'avatar/uuid'=>$_POST['Avatar']['uuid']));
			
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_AVATAR,
												'System error, please contact Jugaogao customer service.');
				
				} else {
					if ($avatar->dfs_avatar_thumb_name !== null && $avatar->dfs_avatar_name !== null) {
						// sync task has been processed successfully before
						// send a good response to the App so that it knows to remove the task
						$response = array(
								'id' => $avatar->id,
								'usn' => $avatar->usn,
								'status_code' => self::RESPONSE_STATUS_GOOD,
								'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
								'error_message' => '',
						);
						Accessory::sendRESTResponse(201, CJSON::encode($response));
					} else {
						$objectExist = true;
					}
				}
			} else {
				$avatar = new Avatar;
				$objectExist = false;
			}
			
			if (isset($_FILES)) {
				$dfsManager = new FDFS();
				
				foreach ($_FILES as $file) {
					$dfsFileHandler = $dfsManager->upload($file['tmp_name']);
					if ($dfsFileHandler === false) {
						// Errors occurred
						$response = array(
										'status_code' => self::RESPONSE_STATUS_BAD,
										'error_message' => 'File upload failure',
									);
						Accessory::warningResponse(self::RESPONSE_STATUS_FILE_UPLOAD_FAILED, 
													'File '. $file['tmp_name'] . ' upload failure');
						
					} else {
						if ($avatar->dfs_group_name === null)
							$avatar->dfs_group_name = $dfsFileHandler['group_name'];
						if (stristr($file['name'], 'thumb')) {
							// thumb file
							$avatar->dfs_avatar_thumb_name = $dfsFileHandler['filename'];
						} else { 
							// normal display file
							$avatar->dfs_avatar_name = $dfsFileHandler['filename'];
						}
					}
				}
			}

			if (!$objectExist) { 
				// 加载User实体，稍后获取USN时使用
				//$user = User::model()->findByPk($user_id);
				foreach ($_POST['Avatar'] as $key => $value) {
					Accessory::writeLog($key . ' ' . $value);
					if ($avatar->hasAttribute($key)) {
						switch ($key) {
							
							case 'last_update_time':
								break;
								
							case 'uuid':
								$avatar->$key = Accessory::packUUID($value);
								break;
									
							default:
								$avatar->$key = $value;
						}
			
					} else {
						switch ($key) {
							case 'user_id':
							case 'server_id':
							case 'usn':
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
			}	
			
			//$createSuccess = true;
			
			$avatar = DBTransactionManager::saveAvatarWithUSN($avatar, $user_id);
			
			if ($avatar !== null) {
				$response = array(
						'id' => $avatar->id,
						'usn' => $avatar->avatar_usn,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				Accessory::writeLog('avatar save failed');
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Avatar sync failed');
			}
		}
	}
	
	private function _updateAvatar()
	{
		
	}
	
	private function _deleteAvatar()
	{
		
	}
	
}
	
