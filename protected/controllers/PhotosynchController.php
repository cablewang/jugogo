<?php 
class PhotosynchController extends Controller
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
	 * process photo synch task sent from the App
	 */
	public function actionProcesssynch()
	{
		switch ($_POST['change_type'])
		{
			case 'create':
				$this->_createPhoto();
				break;
			case 'update':
				$this->_updatePhoto();
				break;
			case 'delete':
				$this->_deletePhoto();
				break;
			default:
				;
		}
	}
	
	private function _createPhoto()
	{
		Accessory::writeLog('create photo' . $_POST['Photo']['user_id']);
		
		if(isset($_POST['Photo']))
		{
			$user_id = $_POST['Photo']['user_id'];
			$note_uuid = pack('h*', $_POST['Photo']['owner_uuid']);
			$photo_uuid = pack('h*', $_POST['Photo']['uuid']);
			$note_id = Note::fetchNoteId($user_id, $note_uuid);
			if ($note_id == NULL) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_NOTE_NOT_EXIST, 
												'Note is not exist', 
												array('photo/note_uuid'=>$note_uuid, 
														'photo/uuid'=>$_POST['Photo']['uuid']));
		
				Accessory::warningResponse(self::RESPONSE_STATUS_NOTE_NOT_EXIST, 
										'System error, please contact Jugaogao customer service.');
				return;
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
				return;
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							"id" => $photo->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
					return;
				}
			} else {
				$photo=new Photo;
			}
			
			if (isset($_FILES)) {
				$filePath = self::JGG_USER_PATH_PREFIX . '/' . 
							$user_id . '/' . 
							Accessory::convertToPhpValue(Note::fetchSubjectUUID($note_id)) . '/' . 
							self::JGG_PHOTO_PATH_POSTFIX;
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
			foreach ($_POST['Photo'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($key === 'user_id' || $key === 'server_id') {
					continue;
				}
				if ($key === 'owner_uuid') {
					$photo->note_id = $note_id;
					continue;
				}
				if ($photo->hasAttribute($key)) {
					switch ($key) {
						case 'last_update_time':
							break;
						case 'uuid':
							$photo->$key = pack('h*', $value);
							break;
						default:
							$photo->$key = $value;
					}
		
				} else {
					Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID, 
												'Parameter is not allowed.');
				}
			}
				
			if($photo->save()) {
				$response = array(
						"id" => $photo->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD, 
											'Photo synch failed');
				return;
			}
		}
	}
	
	private function _updatePhoto()
	{
		
	}
	
	private function _deletePhoto()
	{
		
	}
}
	