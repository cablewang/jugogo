<?php 
require_once('Logging.php');
require_once('Accessory.php');

class VieosynchController extends Controller
{
	
	/**
	 * Default response format
	 */
	Const RESPONSE_DATA_FORMAT = 'json';
	
	Const RESPONSE_STATUS_GOOD = '1';
	Const RESPONSE_STATUS_BAD = '2';
	Const RESPONSE_STATUS_USER_NOT_EXIST = '801';
	Const RESPONSE_STATUS_DUPLICATED_SUBJECT = '802';
	Const RESPONSE_STATUS_SUBJECT_NOT_EXIST = '803';
	Const RESPONSE_STATUS_DUPLICATED_NOTE = '804';
	Const RESPONSE_STATUS_NOTE_NOT_EXIST = '805';
	Const RESPONSE_STATUS_DUPLICATED_VIDEO = '810';
	Const RESPONSE_STATUS_VIDEO_NOT_EXIST = '811';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	Const JGG_LOG_FILE_PATH = 'myphplog.txt'; 
	Const JGG_VIDEO_PATH_POSTFIX = 'video';

	/**
	 * process video synch task sent from the App
	 */
	public function actionProcesssynch()
	{
		switch ($_POST['change_type'])
		{
			case 'create':
				$this->_createVideo();
				break;
			case 'update':
				$this->_updateVideo();
				break;
			case 'delete':
				$this->_deleteVideo();
				break;
			default:
				;
		}
	}
	
	private function _createVideo()
	{
		$log = new Logging();
		$log->lfile(self::JGG_LOG_FILE_PATH);
		
		$photo=new Photo;
		
		if(isset($_POST['Audio']))
		{
			$user_id = $_POST['Audio']['user_id'];
			$note_uuid = pack('h*', $_POST['Audio']['owner_uuid']);
			$audio_uuid = pack('h*', $_POST['Audio']['uuid']);
			$note_id = _fetchNoteId($user_id, $note_uuid);
			if ($note_id == NULL) {
		
				$this->sendErrorMessageToAdmin(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'Note is not exist',
						array('photo/note_uuid'=>$note_uuid,
								'photo/uuid'=>$_POST['Audio']['uuid']));
		
				$this->_warningResponse(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'System error, please contact Jugaogao customer service.');
				return;
			}
		
			if (Video::isVideoExist($note_id, $video_uuid)) {
					
				$this->sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_VIDEO,
						'Duplicated Audio',
						array('video/note_id'=>$note_id,
								'video/uuid'=>$_POST['Video']['uuid']));
					
				$this->_warningResponse(self::RESPONSE_STATUS_DUPLICATED_VIDEO,
						'System error, please contact Jugaogao customer service.');
				return;
			}
		
			if (isset($_FILES)) {
				$filePath = self::$user_id . '/' . Note::fetchSubjectId($note_id) .'/' . JGG_VIDEO_PATH_POSTFIX;
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
						$this->_warningResponse(self::RESPONSE_STATUS_FILE_UPLOAD_FAILED,
								'File upload failure');
						return;
					}
				}
			}
		
			//$model->attributes=$_POST['Subject'];
			foreach ($_POST['Video'] as $key => $value) {
				$log->lwrite($key . ' ' . $value);
				if ($video->hasAttribute($key)) {
					switch ($key) {
						case 'last_update_time':
							break;
						case 'uuid':
							$video->$key = pack('h*', $value);
							break;
						default:
							$video->$key = $value;
					}
		
				} else {
					$this->_warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
							'Parameter is not allowed.');
				}
			}
		
			if($video->save()) {
				$response = array(
						"id" => $video->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				$this->_sendResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				$this->_warningResponse(self::RESPONSE_STATUS_BAD,
						'Video synch failed');
				return;
			}
		}
	}
	
	private function _updateVideo()
	{
		
	}
	
	private function _deleteVideo()
	{
		
	}
	
	private function _warningResponse($statusCode, $message)
	{
		$response = array(
				'status_code' => $statusCode,
				'error_message' => $message,
		);
		$this->_sendResponse(500, CJSON::encode($response));
	}
	
	private function _sendResponse ($status = 200, $body = '', $content_type = 'text/html')
	{
		// set the status
		$status_header = 'HTTP/1.1' . $status . ' ' . Accessory::getStatusCodeMessage($status);
		header($status_header);
		// and the content type
		header('Content-type: ' . $content_type);
	
		echo $body;
		Yii::app()->end();
	}
}