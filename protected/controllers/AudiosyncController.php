<?php 

require_once(APP_ROOT.'/fastdfs/fastDFS.php');

class AudiosyncController extends Controller
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
	Const RESPONSE_STATUS_DUPLICATED_AUDIO = '808';
	Const RESPONSE_STATUS_AUDIO_NOT_EXIST = '809';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	Const JGG_AUDIO_PATH_POSTFIX = 'audio';
	Const JGG_USER_PATH_PREFIX = 'private';
	
	
	/**
	 * process audio synch task sent from the App
	 */
	public function actionCreate()
	{
		if(isset($_POST['Audio']))
		{
			$user_id = $_POST['Audio']['user_id'];
			$note_id = $_POST['Audio']['note_id'];
			Accessory::writeLog('note id: '. $note_id);
			$audio_uuid = Accessory::packUUID($_POST['Audio']['uuid']);
			if (! Note::isNoteExist($note_id)) {
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
							'Note '. $note_id . ' is not exist',
						array('audio/note_id'=>$note_id,
								'audio/uuid'=>$_POST['Audio']['uuid']));
		
				Accessory::warningResponse(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'System error, please contact Jugaogao customer service.');
			}
			$audio = Audio::model()->findByAttributes(array('note_id'=>$note_id, 'uuid'=>$audio_uuid));
			if ($audio !== NULL) {
				if (strtotime($audio->create_time) !== strtotime($_POST['Audio']['create_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_AUDIO,
							'Duplicated Audio',
							array('audio/note_id'=>$note_id,
									'audio/uuid'=>$_POST['Audio']['uuid']));
						
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_AUDIO,
							'System error, please contact Jugaogao customer service.');
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							'id' => $audio->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
				}
			} else {
				$audio = new Audio;
			}
				
			if (isset($_FILES)) {
				
				$dfsManager = new FDFS();
				
				foreach ($_FILES as $file) {
					//Accessory::writeLog($file['tmp_name']);
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
						if ($audio->dfs_group_name === null)
							$audio->dfs_group_name = $dfsFileHandler['group_name'];
						if (stristr($file['name'], 'mp3')) {
							// thumb file
							$audio->dfs_mp3_file_name = $dfsFileHandler['filename'];
						} else {
							// original file
							$audio->dfs_original_file_name = $dfsFileHandler['filename'];
						}
					}
				}
			}
		
			foreach ($_POST['Audio'] as $key => $value) {
				Accessory::writeLog($key . ': ' . $value);
				if ($audio->hasAttribute($key)) {
					switch ($key) {
						
						case 'last_update_time':
							break;
							
						case 'uuid':
							$audio->$key = $audio_uuid;
							break;
							
						default:
							$audio->$key = $value;
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
		
			if($audio->save()) {
				$response = array(
						'id' => $audio->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Audio synch failed');
			}
		}
	}
	
}
	
