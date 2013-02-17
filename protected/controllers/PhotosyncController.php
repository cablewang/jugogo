<?php 

require_once(APP_ROOT.'/fastdfs/fastDFS.php');

class PhotosyncController extends Controller
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
	Const RESPONSE_STATUS_DUPLICATED_PHOTO = '806';
	Const RESPONSE_STATUS_PHOTO_NOT_EXIST = '807';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	Const JGG_PHOTO_PATH_POSTFIX = 'photo';
	Const JGG_USER_PATH_PREFIX = 'private';

	/**
	 * 创建Photo实体
	 */
	public function actionCreate()
	{
		Accessory::writeLog('create photo for user: ' . $_POST['Photo']['user_id']);
		
		if(isset($_POST['Photo']))
		{
			$user_id = $_POST['Photo']['user_id'];
			$note_id = $_POST['Photo']['note_id'];
			Accessory::writeLog($note_id);
			$photo_uuid = Accessory::packUUID($_POST['Photo']['uuid']);
			if (!Note::isNoteExist($note_id)) {
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_NOTE_NOT_EXIST, 
												'Note is not exist', 
												array('photo/note_id'=>$note_id, 
														'photo/uuid'=>$_POST['Photo']['uuid']));
		
				Accessory::warningResponse(self::RESPONSE_STATUS_NOTE_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
			}

			$photo = Photo::model()->findByAttributes(array('note_id'=>$note_id, 'uuid'=>$photo_uuid));
			if ($photo !== NULL) {
				if (strtotime($photo->create_time) !== strtotime($_POST['Photo']['create_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_PHOTO,
														'Duplicated Photo',
														array('photo/note_id'=>$note_id,
														'photo/uuid'=>$_POST['Photo']['uuid']));
			
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_PHOTO,
												'System error, please contact Jugaogao customer service.');
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							'id' => $photo->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
				}
			} else {
				$photo = new Photo;
			}
			
			if (isset($_FILES)) {
				
				$dfsManager = new FDFS();
				
				foreach ($_FILES as $file) {
					//Accessory::writeLog($file['name']);
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
						if ($photo->dfs_group_name === null)
							$photo->dfs_group_name = $dfsFileHandler['group_name'];
						if (stristr($file['name'], 'thumb')) {
							// thumb file
							$photo->dfs_thumb_name = $dfsFileHandler['filename'];
						} elseif (stristr($file['name'], 'display')) {
							// normal display file
							$photo->dfs_display_file_name = $dfsFileHandler['filename'];
						} else {
							// original file
							$photo->dfs_original_file_name = $dfsFileHandler['filename'];
						}
					}
				}
				
			}
				
			foreach ($_POST['Photo'] as $key => $value) {
				Accessory::writeLog($key . ': ' . $value);
				
				if ($photo->hasAttribute($key)) {
					switch ($key) {
						
						case 'last_update_time':
							break;
							
						case 'uuid':
							$photo->$key = $photo_uuid;
							break;
							
						default:
							$photo->$key = $value;
					}
		
				} else {
					switch ($key) {
						case 'user_id':
						case 'server_id':
							break;
						
						default:
							Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
									'Parameter is not allowed.');
					}
					
				}
			}
				
			if($photo->save()) {
				$response = array(
						'id' => $photo->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD, 
											'Photo synch failed');
			}
		}
	}
	
	
}
	
