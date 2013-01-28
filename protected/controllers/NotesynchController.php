<?php 
class NotesynchController extends Controller
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
	Const JGG_IMAGE_PATH_PREFIX = 'images/';
	
	/**
	 * process note synch task sent from the App
	 */
	public function actionProcesssynch()
	{
		switch ($_POST['change_type'])
		{
			case 'create':
				$this->_createNote();
				break;
			case 'update':
				$this->_updateNote();
				break;
			case 'delete':
				$this->_deleteNote();
				break;
			default:
				;
		}
	}
	
	private function _createNote()
	{
		if(isset($_POST['Note']))
		{
			$user_id = $_POST['Note']['user_id'];
			$subject_uuid = pack('h*', $_POST['Note']['owner_uuid']);
			$note_uuid = pack('h*', $_POST['Note']['uuid']);
			if (!User::isUserExist($user_id)) {
	
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST,
						'user is not exist',
						array('note/user_id'=>$user_id,
								'note/uuid'=>$_POST['Note']['uuid']));
	
				Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST, 'System error,
						please contact Jugaogao customer service.');
				return;
			}
	
			$note = Note::model()->findByAttributes(array('user_id'=>$user_id, 'uuid'=>$note_uuid));
			if ($note !== NULL) {
				if (strtotime($note->save_time) !== strtotime($_POST['Note']['save_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_NOTE,
							'duplicated Note',
							array('note/uuid'=>$_POST['Note']['uuid']));
		
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_NOTE,
							'System error, please contact Jugaogao customer service.');
					return;
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							"id" => $note->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
					return;
				}
			} else {
				$note = new Note;
			}
				
			foreach ($_POST['Note'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($key === 'subject_uuid') {
					$note->subject_id = Subject::fetchSubjectId($user_id, $subject_uuid);
					continue;
				}
				if ($key === 'owner_uuid') {
					continue;
				}
				if ($note->hasAttribute($key)) {
					switch ($key) {
						case 'last_update_time':
							break;
						case 'uuid':
							$note->$key = pack('h*', $value);
							break;
						default:
							$note->$key = $value;
					}
	
				} else {
					Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
							'Parameter is not allowed.');
				}
			}
			
			foreach ($note->getAttributes() as $key => $value) {
				Accessory::writeLog('note attribute: ' . $key . ' ' . $value);
			}
			
			if($note->save()) {
				$response = array(
						'id' => $note->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Note synch failed');
				return;
			}
		}
	}
	
	private function _updateNote()
	{
	
	}
	
	private function _deleteNote()
	{
	
	}
}