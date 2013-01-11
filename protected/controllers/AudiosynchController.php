<?php 
require_once('Logging.php');
require_once('Accessory.php');

class AudiosynchController extends Controller
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
	Const JGG_LOG_FILE_PATH = 'myphplog.txt'; 
	Const JGG_AUDIO_PATH_POSTFIX = 'audio';
	Const JGG_USER_PATH_PREFIX = 'private';
	
	
	/**
	 * process audio synch task sent from the App
	 */
	public function actionProcesssynch()
	{
		switch ($_POST['change_type'])
		{
			case 'create':
				$this->_createAudio();
				break;
			case 'update':
				$this->_updateAudio();
				break;
			case 'delete':
				$this->_deleteAudio();
				break;
			default:
				;
		}
	}
	
	private function _createAudio()
	{
		$log = new Logging();
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		if(isset($_POST['Audio']))
		{
			$user_id = $_POST['Audio']['user_id'];
			$note_uuid = pack('h*', $_POST['Audio']['owner_uuid']);
			$audio_uuid = pack('h*', $_POST['Audio']['uuid']);
			$note_id = Note::fetchNoteId($user_id, $note_uuid);
			if ($note_id === NULL) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'Note is not exist',
						array('audio/note_uuid'=>$note_uuid,
								'audio/uuid'=>$_POST['Audio']['uuid']));
		
				Accessory::warningResponse(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'System error, please contact Jugaogao customer service.');
				return;
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
					return;	
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							"id" => $audio->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
					return;
				}
			} else {
				$audio = new Audio;
			}
				
			if (isset($_FILES)) {
				$filePath = self::JGG_USER_PATH_PREFIX . '/' . 
							$user_id . '/' . 
							Accessory::convertToPhpValue(Note::fetchSubjectUUID($note_id)) . '/' . 
							self::JGG_AUDIO_PATH_POSTFIX;
				
				$log->lwrite('subject id: ' . Note::fetchSubjectId($note_id) . 'subject uuid: ' . Note::fetchSubjectUUID($note_id));
				
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
						return;
					}
				}
			}
		
			//$model->attributes=$_POST['Subject'];
			foreach ($_POST['Audio'] as $key => $value) {
				$log->lwrite($key . ' ' . $value);
				if ($key === 'user_id' || $key === 'server_id') {
					continue;
				}
				if ($key === 'owner_uuid') {
					$audio->note_id = $note_id;
					continue;
				}
				if ($audio->hasAttribute($key)) {
					switch ($key) {
						case 'last_update_time':
							break;
						case 'uuid':
							$audio->$key = pack('h*', $value);
							break;
						default:
							$audio->$key = $value;
					}
		
				} else {
					Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
							'Parameter is not allowed.');
				}
			}
		
			if($audio->save()) {
				$response = array(
						"id" => $audio->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Audio synch failed');
				return;
			}
		}
	}
	
	private function _updateAudio()
	{
	
	}
	
	private function _deleteAudio()
	{
	
	}
}
	