<?php
class RestController extends Controller
{

	// constants for event validate type
	Const EVENT_VALIDATE_TYPE_REST_PASSWORD = 1;
	
	// 虚拟客服的邮箱地址和名字。为了让用户感到情切，用女人名Anna
	Const JGG_CUSTOMER_SERVICE_EMAIL_ADDRESS = 'anna@jugaogao.com';
	Const JGG_CUSTOMER_SERVICE_REPRESENTATIVE_NAME = 'Anna';

	Const RESPONSE_STATUS_GOOD = '1';
	Const RESPONSE_STATUS_BAD = '2';
	Const RESPONSE_STATUS_INSUFFICIENT_PARAM = '103';
	Const RESPONSE_STATUS_USER_NOT_EXIST = '801';
	
	/**
	 * Default response format
	 * either 'json' or 'xml'
	 */
	private $format = 'json';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
		array('allow',  // allow all users to perform 'authkey' actions
				'actions'=>array('authkey', 'syncstatus', 'syncchunk', 'attachmentdata'),
				'users'=>array('*'),
		),
		array('deny',  // deny all users
				'users'=>array('*'),
		),
		);
	}
	
	/**
	 * receives request from client and sends back an authentication key
	 */
	public function actionAuthkey()
	{
		$client_id = Accessory::packUUID($_GET['id']);
		
		$actionAuth = new Actionauth;
		$actionAuth->client_sub_id = $client_id;
		$phpuuid = strtoupper(Accessory::gen_uuid());
		$actionAuth->key = Accessory::packUUID($phpuuid);
		date_default_timezone_set("UTC");
		$actionAuth->create_time = date('Y-m-d H:i:s');
		
		if ($actionAuth->save()) {
			$response = array(
				'authkey' => $phpuuid,
			);
			Accessory::writeLog(get_class($this) . '-> auth key ' . ': ' . $response['authkey'] . ', ' . Accessory::unpackUUID($actionAuth->key));
			Accessory::sendRESTResponse(201, CJSON::encode($response));
		} else {
			// Errors occurred
			Accessory::warningResponse(RESPONSE_STATUS_BAD, '创建授权令牌失败');
		}
	}
	
	/**
	 * return sync status for specific account
	 */
	public function actionSyncstatus()
	{
		Accessory::writeLog(get_class($this) . '-> sync status, user id: ' . $_GET['id']);
		
		$user_id = $this->validateUserID();
		$user = User::model()->findByPk($user_id);
		$response = array(
					'update_count' => $user->update_count,
					'full_sync_before' => $user->full_sync_before,
				);
		Accessory::writeLog('sending response: full_sync_before' . $response["full_sync_before"]);
		Accessory::sendRESTResponse(201, CJSON::encode($response));
	}
	
	/**
	 * 返回指定起点和数量的同步数据
	 */
	public function actionSyncchunk()
	{
		Accessory::writeLog(get_class($this) . '-> sync chunk user id: ' . $_GET['id']);
		
		$user_id = $this->validateUserID();
		$user = User::model()->findByPk($user_id);
		$start_usn = $_GET['usn'];
		$max = $_GET['max'];
		$end_usn = $start_usn + $max - 1;
		
		$criteria = new CDbCriteria;
		$criteria->condition = "user_id=:userID AND usn>=:startUSN AND usn<=:endUSN";
		$criteria->params = array(':userID' => $user_id, ':startUSN' => $start_usn, ':endUSN' => $end_usn);
		
		$tags = $this->fetchTags($user_id, $start_usn, $end_usn);
		$subjects = $this->fetchSubjects($user_id, $start_usn, $end_usn);
		$notes = $this->fetchNotes($criteria);
		$avatars = $this->fetchAvatars($user_id, $start_usn, $end_usn);
		
		$response = array(
					'status_code' => self::RESPONSE_STATUS_GOOD,
					'tags' => $tags,
					'subjects' => $subjects,
					'avatars' => $avatars,
					'notes' => $notes,
				);
		Accessory::sendRESTResponse(201, CJSON::encode($response));
	}
	
	private function fetchTags($user_id, $start_usn, $end_usn)
	{
		$criteria = new CDbCriteria;
		$criteria->with = array('users');
		$criteria->condition = "user_id=:userID AND usn>=:startUSN AND usn<=:endUSN";
		$criteria->params = array(':userID' => $user_id, ':startUSN' => $start_usn, ':endUSN' => $end_usn);
		return Tag::model()->findAll($criteria);
	}

	private function fetchAvatars($user_id, $start_usn, $end_usn)
	{
		$subjects = Subject::fetchAllOwnSubjects($user_id);
		$subject_ids = array();
		foreach ($subjects as $subject) {
			array_push($subject_ids, $subject->id);
			Accessory::writeLog('subject id: ' . $subjects[0]->id);
		}
		$criteria = new CDbCriteria;
		$criteria->with = array('subject');
		$criteria->condition = "subject_id=:subjectID AND avatar_usn>=:startUSN AND avatar_usn<=:endUSN";
		$criteria->params = array(':subjectID' => $subjects[0]->id, ':startUSN' => $start_usn, ':endUSN' => $end_usn);
		$avatars = Avatar::model()->findAll($criteria);
		
		$avatarsDataArray = array();
		
		//返回解压缩的UUID
		foreach ($avatars as $avatar) {
			$avatarData = $avatar->getAttributes();
			$avatarData['uuid'] = Accessory::unpackUUID($avatar->uuid);
			array_push($avatarsDataArray, $avatarData); 
		}
		return $avatarsDataArray;
	}
	
	private function fetchSubjects($user_id, $start_usn, $end_usn)
	{
		$criteria = new CDbCriteria;
		$criteria->with = array('users');
		$criteria->condition = "user_id=:userID AND role=1 AND usn>=:startUSN AND usn<=:endUSN";
		$criteria->params = array(':userID' => $user_id, ':startUSN' => $start_usn, ':endUSN' => $end_usn);
		$subjects = Subject::model()->findAll($criteria);
		
		$subjectsDataArray = array();
		
		//返回解压缩的UUID
		foreach ($subjects as $subject) {
			$subjectData = $subject->getAttributes();
			$subjectData['uuid'] = Accessory::unpackUUID($subject->uuid);
			array_push($subjectsDataArray, $subjectData);
		}
		return $subjectsDataArray;
	}
	
	private function fetchNotes($criteria)
	{
		$notes = Note::model()->findAll($criteria);
		$notesDataArray = array();
		
		//返回解压缩的UUID
		foreach ($notes as $note) {
			$noteData = $note->getAttributes();
			$noteData['uuid'] = Accessory::unpackUUID($note->uuid);
			array_push($notesDataArray, $noteData);
		}
		return $notesDataArray;
	}
	
	public function actionAttachmentdata()
	{
		$note_id = $_GET['id'];
		Accessory::writeLog(get_class($this) . '-> download attachment data for note #: ' . $note_id);
		$note = Note::model()->findByPk($note_id);
		$photoDataArray = array();
		if (count($note->photos) > 0) {
			foreach ($note->photos as $photo) {
				if ($photo->deleted != 1) {
					$photoData = $photo->getAttributes();
					$photoData['uuid'] = Accessory::unpackUUID($photo->uuid);
					array_push($photoDataArray, $photoData);
				}
			}
		}
		$audioDataArray = array();
		if (count($note->audios) > 0) {
			foreach ($note->audios as $audio) {
				if ($audio->deleted != 1) {
					$audioData = $audio->getAttributes();
					$audioData['uuid'] = Accessory::unpackUUID($audio->uuid);
					array_push($audioDataArray, $audioData);
				}
			}
		}
		$response = array(
				'status_code' => self::RESPONSE_STATUS_GOOD,
				'photo_data' => $photoDataArray,
				'audio_data' => $audioDataArray,
		);
		Accessory::sendRESTResponse(201, CJSON::encode($response));
	}
	
	/**
	 * 	检查客户端发送来的用户ID是否有效
	 */
	private function validateUserID()
	{
		if(!isset($_GET['id']))
		{
			Accessory::warningResponse(self::RESPONSE_STATUS_INSUFFICIENT_PARAM,
			'Insufficient parameter.');
		} else {
			$user_id = $_GET['id'];
		}
		Accessory::writeLog('we are here! ' . $user_id);
		if (!User::isUserExist($user_id)) {
			Accessory::sendErrorMessageToAdmin(self::RESPONSE_STATUS_USER_NOT_EXIST,
			'user is not exist',
			array('syncstatus/user_id'=>$user_id));
		
			Accessory::warningResponse(self::RESPONSE_STATUS_USER_NOT_EXIST,
			'系统错误, 请联系举高高客服。');
		}
		return $user_id;
	}
	
	/**
	 * receives request from client and sends back an authentication key
	 */
	public function actionRestpassword()
	{
		$log = new Logging();
		
		// fetch and decode the PUT parameters
		$json = file_get_contents('php://input');
		$put_vars = CJSON::decode($json, true);
		$log->lfile(self::JGG_LOG_FILE_PATH);
		

		Accessory::writeLog($put_vars[id] . ' ' . $put_vars[password]);

	
		$user_id = $put_vars["id"];
		$validate = new Eventvalidate;
		$validate->user_id = $user_id;
		$validate->type = EVENT_VALIDATE_TYPE_REST_PASSWORD;
		$phpuuid = Accessory::gen_uuid();
		$validate->uuid = pack('h*', $phpuuid);
		date_default_timezone_set("UTC");
		$validate->create_time = date('Y-m-d H:i:s');
	
		if ($validate->save()) {
			$user = User::model()->findByPk($user_id);
			
			Accessory::sendEmail($user->email, "test", JGG_CUSTOMER_SERVICE_EMAIL_ADDRESS, 
					JGG_CUSTOMER_SERVICE_REPRESENTATIVE_NAME, 'test', 'test');
			$response = array(
					'result_code' => 1,
			);
			Accessory::sendRESTResponse(201, CJSON::encode($response));
		} else {
			// Errors occurred
			Accessory::warningResponse(RESPONSE_STATUS_BAD, 'Reset password failed');
		}
	}
	
}
