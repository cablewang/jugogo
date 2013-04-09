<?php

class SyncAccessory
{

	Const RESPONSE_STATUS_GOOD = '1';
	Const SYNC_STATUS_TASK_DONE_BEFORE = '300';
	
	public static function deleteSyncTaskDoneBeforeFor($object)
	{
		Accessory::writeLog('we are here!');
		// sync task has been processed successfully before
		// send a good response to the App so that it knows to remove the task
		$response = array(
				'id' => $object->id,
				'usn' => $object->usn,
				'status_code' => self::RESPONSE_STATUS_GOOD,
				'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
				'error_message' => '',
		);
		Accessory::sendRESTResponse(201, CJSON::encode($response));
	}
	
	public static function deleteSyncTaskDoneBeforeForAvatar($avatar)
	{
		Accessory::writeLog('we are here!');
		// sync task has been processed successfully before
		// send a good response to the App so that it knows to remove the task
		$response = array(
				'id' => $avatar->id,
				'usn' => $avatar->avatar_usn,
				'status_code' => self::RESPONSE_STATUS_GOOD,
				'sync_status_code' => self::SYNC_STATUS_TASK_DONE_BEFORE,
				'error_message' => '',
		);
		Accessory::sendRESTResponse(201, CJSON::encode($response));
	}
}