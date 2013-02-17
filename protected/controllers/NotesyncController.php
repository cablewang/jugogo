<?php 
class NotesyncController extends Controller
{
	
	/**
	 * Default response format
	 */
	Const RESPONSE_DATA_FORMAT = 'json';
	
	Const RESPONSE_STATUS_GOOD = '1';
	Const RESPONSE_STATUS_BAD = '2';
	Const SYNC_STATUS_TASK_DONE_BEFORE = '300';
	Const SYNC_STATUS_OBJECT_DELETED = '301';
	Const SYNC_STATUS_NEED_INCREMENT_SYNC = '302';
	Const RESPONSE_STATUS_USER_NOT_EXIST = '801';
	Const RESPONSE_STATUS_DUPLICATED_SUBJECT = '802';
	Const RESPONSE_STATUS_SUBJECT_NOT_EXIST = '803';
	Const RESPONSE_STATUS_DUPLICATED_NOTE = '804';
	Const RESPONSE_STATUS_NOTE_NOT_EXIST = '805';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	Const RESPONSE_STATUS_WRONG_METHOD = '104';
	Const JGG_IMAGE_PATH_PREFIX = 'images/';
	
	public function actionCreate()
	{
		if(isset($_POST['Note']))
		{
			$user_id = $_POST['Note']['user_id'];
			$subject_id = $_POST['Note']['subject_id'];
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
	
			$note = Note::model()->findByAttributes(array('user_id'=>$user_id, 'subject_id'=>$subject_id, 'uuid'=>$note_uuid));
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
							'id' => $note->id,
							'usn' => $note->usn,
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
				if ($note->hasAttribute($key)) {
					switch ($key) {
						
						case 'last_update_time':
							break;
							
						case 'uuid':
							$note->$key = Accessory::packUUID($value);
							break;
							
						case 'usn':
							break;
							
						default:
							$note->$key = $value;
					}
	
				} else {
					Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
							'Parameter ' . $key . 'is not allowed.');
				}
			}
			
			$note = DBTransactionManager::saveObjectWithUSN($note, $user_id);
			if($note !== null) {
				$response = array(
						'id' => $note->id,
						'usn' => $note->usn,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Note sync failed');
			}
		}
	}

	public function actionUpdate()
	{
		if(isset($_GET['id']))
		{
			$note_id = $_GET['id'];
			Accessory::writeLog('note id: ' . $note_id);
			
			$user_id = $_POST['Note']['user_id'];
			Accessory::writeLog($user_id);
			
			$subject_id = $_POST['Note']['subject_id'];
			if (!User::isUserExist($user_id)) {
	
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST,
						'user is not exist',
				array('note/user_id'=>$user_id,
						'note/id'=>$_POST['Note']['server_id']));
	
				Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST, 'System error,
						please contact Jugaogao customer service.');
			}
	
			$note = Note::model()->findByPk($note_id);
			if ($note !== NULL) {
				if (strtotime($note->save_time) !== strtotime($_POST['Note']['save_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_NOTE,
						'duplicated Note',
					array('note/id'=>$_POST['Note']['server_id']));
	
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_NOTE,
						'System error, please contact Jugaogao customer service.');
				} else {
					if ($note->usn == $_POST['Note']['usn']) {
						$this->_syncTaskDoneBefore($note);
					}
				}
			} else {
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_WRONG_METHOD,
						Accessory::getRESTStatusMessage(self::RESPONSE_STATUS_WRONG_METHOD),
						array('note/id'=>$_POST['Note']['server_id']));
	
				Accessory::warningResponse(self::RESPONSE_STATUS_WRONG_METHOD,
						'System error, please contact Jugaogao customer service.');
			}
	
			foreach ($_POST['Note'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($note->hasAttribute($key)) {
					switch ($key) {
								
						case 'uuid':
							$note->$key = Accessory::packUUID($value);
							break;
								
						case 'usn':
							break;
								
						default:
							$note->$key = $value;
					}
	
				} else {
					if ($key == 'server_id') {
						;
					} else {
						Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
								'Parameter ' . $key . ' is not allowed.');
					}
				}
			}

			// update 
			$note = DBTransactionManager::saveObjectWithUSN($note, $user_id);
			
			if($note !== null) {
				$response = array(
						'id' => $note->id,
						'usn' => $note->usn,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
							'Note sync failed');
			}
		}
	}
	
	public function actionDelete() {
		$note_id = $_GET['id'];
		$usn = $_GET['usn'];
		
		Accessory::writeLog('note id: ' . $note_id . '; usn: ' . $usn);
		
		$note = Note::model()->findByPk($note_id);
		$user_id = $note->user->id;
		if ($note->deleted == 1) {
			$this->_syncTaskDoneBefore($note);
		} elseif ($note->usn == $usn) {
			if (DBTransactionManager::deleteNoteAndAttachments($note, $user_id)) {
				$response = array(
						'id' => $note->id,
						'usn' => $note->usn,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'sync_status_code' => self::SYNC_STATUS_OBJECT_DELETED,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
											'Note sync failed');	
			}
		} elseif ($note->usn > $usn) {
			// 服务器端的数据比客户端的数据更新
			// 提示客户端进行增量同步
			Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
										'Delete target usn is newer than device\'s',
										self::SYNC_STATUS_NEED_INCREMENT_SYNC);
		} else {
			Accessory::writeLog('this should not happen!');
			// Errors occurred
			Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
							'Note sync failed');
		}
	}
	
	private function _syncTaskDoneBefore($note)
	{
		// sync task has been processed successfully before
		// send a good response to the App so that it knows to remove the task
		$response = array(
				'id' => $note->id,
				'usn' => $note->usn,
				'status_code' => self::RESPONSE_STATUS_GOOD,
				'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
				'error_message' => '',
		);
		Accessory::sendRESTResponse(201, CJSON::encode($response));
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
