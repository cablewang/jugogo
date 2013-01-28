<?php 
class WeathersynchController extends Controller
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
	Const RESPONSE_STATUS_DUPLICATED_WEATHER = '812';
	Const RESPONSE_STATUS_WEATHER_NOT_EXIST = '813';
	Const RESPONSE_STATUS_PARAM_INVALID = '101';
	Const RESPONSE_STATUS_FILE_UPLOAD_FAILED = '102';
	
	/**
	 * process weather synch task sent from the App
	 */
	public function actionProcesssynch()
	{
		switch ($_POST['change_type'])
		{
			case 'create':
				$this->_createWeather();
				break;
			case 'update':
				$this->_updateWeather();
				break;
			case 'delete':
				$this->_deleteWeather();
				break;
			default:
				;
		}
	}
	
	private function _createWeather()
	{
		if(isset($_POST['Weather']))
		{
			$user_id = $_POST['Weather']['user_id'];
			$note_uuid = pack('h*', $_POST['Weather']['owner_uuid']);
			$weather_uuid = pack('h*', $_POST['Weather']['uuid']);
			$note_id = Note::fetchNoteId($user_id, $note_uuid);
			if ($note_id == NULL) {
		
				Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'Note is not exist',
						array('weather/note_uuid'=>$note_uuid,
								'weather/uuid'=>$_POST['Weather']['uuid']));
		
				Accessory::warningResponse(self::RESPONSE_STATUS_NOTE_NOT_EXIST,
						'System error, please contact Jugaogao customer service.');
				return;
			}
			
			$weather = Weather::model()->findByAttributes(array('note_id'=>$note_id, 'uuid'=>$weather_uuid));
			if (Weather::isWeatherExist($note_id, $weather_uuid)) {
				if (strtotime($weather->create_time) !== strtotime($_POST['Weather']['create_time'])) {
					Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_DUPLICATED_WEATHER,
							'Duplicated Weather',
							array('weather/note_id'=>$note_id,
									'weather/uuid'=>$_POST['Weather']['uuid']));
						
					Accessory::warningResponse(self::RESPONSE_STATUS_DUPLICATED_WEATHER,
							'System error, please contact Jugaogao customer service.');
					return;
				} else {
					// sync task has been processed successfully before
					// send a good response to the App so that it knows to remove the task
					$response = array(
							"id" => $weather->id,
							'status_code' => self::RESPONSE_STATUS_GOOD,
							'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
							'error_message' => '',
					);
					Accessory::sendRESTResponse(201, CJSON::encode($response));
					return;
				}	
			} else {
				$weather=new Weather;
			}
		
			//$model->attributes=$_POST['Subject'];
			foreach ($_POST['Weather'] as $key => $value) {
				Accessory::writeLog($key . ' ' . $value);
				if ($key === 'user_id' || $key === 'server_id') {
					continue;
				}
				if ($key === 'owner_uuid') {
					$weather->note_id = $note_id;
					continue;
				}
				if ($weather->hasAttribute($key)) {
					switch ($key) {
						case 'last_update_time':
							break;
						case 'uuid':
							$weather->$key = pack('h*', $value);
							break;
						default:
							$weather->$key = $value;
					}
		
				} else {
					Accessory::warningResponse(self::RESPONSE_STATUS_PARAM_INVALID,
							'Parameter is not allowed.');
				}
			}
		
			if($weather->save()) {
				$response = array(
						"id" => $weather->id,
						'status_code' => self::RESPONSE_STATUS_GOOD,
						'error_message' => '',
				);
				Accessory::sendRESTResponse(201, CJSON::encode($response));
				return;
			} else {
				// Errors occurred
				Accessory::warningResponse(self::RESPONSE_STATUS_BAD,
						'Weather synch failed');
				return;
			}
		}
	}
	
	private function _updateWeather()
	{
	
	}
	
	private function _deleteWeather()
	{
	
	}
	
}