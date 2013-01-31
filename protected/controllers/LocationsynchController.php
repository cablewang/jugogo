<?php 
class LocationsynchController extends Controller
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
	Const RESPONSE_STATUS_DUPLICATED_LOCATION = '814';
	Const RESPONSE_STATUS_LOCATION_NOT_EXIST = '815';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	Const JGG_IMAGE_PATH_PREFIX = 'images/';

	/**
	 * process location synch task sent from the App
	 */
	public function actionProcesssynch()
	{
		switch ($_POST['change_type'])
		{
			case 'create':
				$this->_createLocation();
				break;
			case 'update':
				$this->_updateLocation();
				break;
			case 'delete':
				$this->_deleteLocation();
				break;
			default:
				;
		}
	}
	
	private function _createLocation()
	{
		$weather=new Weather;
		
		if(isset($_POST['Location']))
		{
			$user_id = $_POST['Location']['user_id'];
			$note_uuid = pack('h*', $_POST['Location']['owner_uuid']);
			$location_uuid = pack('h*', $_POST['Location']['uuid']);
			$note_id = _fetchNoteId($user_id, $note_uuid);
			if ($note_id == NULL) {
		
				$this->sendErrorMessageToAdmin(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'Note is not exist',
						array('location/note_uuid'=>$note_uuid,
								'location/uuid'=>$_POST['Location']['uuid']));
		
				$this->_warningResponse(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'System error, please contact Jugaogao customer service.');
				return;
			}
		
			if (Location::isLocationExist($note_id, $location_uuid)) {
					
				$this->sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_LOCATION,
						'Duplicated location',
						array('location/note_id'=>$note_id,
								'location/uuid'=>$_POST['Location']['uuid']));
					
				$this->_warningResponse(self::RESPONSE_STATUS_DUPLICATED_LOCATION,
						'System error, please contact Jugaogao customer service.');
				return;
			}
		
			//$model->attributes=$_POST['Subject'];
			foreach ($_POST['Location'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($location->hasAttribute($key)) {
					switch ($key) {
						case 'last_update_time':
							break;
						case 'uuid':
							$location->$key = pack('h*', $value);
							break;
						default:
							$location->$key = $value;
					}
		
				} else {
					$this->_warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
							'Parameter is not allowed.');
				}
			}
		
			if($location->save()) {
				$response = array(
						"id" => $location->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				$this->_sendResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				$this->_warningResponse(self::RESPONSE_STATUS_BAD,
						'Location synch failed');
				return;
			}
		}		
	}
	
	private function _updateLocation()
	{
		
	}
	
	private function _deleteLocation()
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
