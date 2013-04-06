<?php
class DBTransactionManager
{
	public static function saveObjectWithUSN($object, $user_id)
	{
		$transaction = Yii::app()->db->beginTransaction();
		try {
			$user = User::model()->findByPk($user_id);
			$object->usn = $user->update_count + 1;
			$object->save();
			$user->increaseUSN();
			$transaction->commit();
			return $object;
		} 
		catch (StaleObjectError $staleObjectError) {
			Accessory::writeLog('update usn failed');
			$this->saveObjectWithUSN($object, $user_id);
		}
		catch (Exception $e) {
			return null;
		}
		
	}
	
	public static function saveAvatarWithUSN($avatar, $user_id)
	{
		$transaction = Yii::app()->db->beginTransaction();
		try {
			$user = User::model()->findByPk($user_id);
			$avatar->avatar_usn = $user->update_count + 1;
			$avatar->save();
			$user->increaseUSN();
			$transaction->commit();
			return $avatar;
		}
		catch (StaleObjectError $staleObjectError) {
			Accessory::writeLog('update usn failed');
			$this->saveAvatarWithUSN($avatar, $user_id);
		}
		catch (Exception $e) {
			Accessory::writeLog('save error!' . $e->getMessage());
			return null;
		}
	
	}
	
	public static function deleteNoteAndAttachments($note, $user_id)
	{
		$transaction = Yii::app()->db->beginTransaction();
		try {
			$attachments = $note->fetchAllAttachments();
			foreach ($attachments as $attachment) {
				$attachment->deleted = 1;
				$attachment->save();
			}
			$note->deleted = 1;
			$user = User::model()->findByPk($user_id);
			$note->usn = $user->update_count + 1;
			$user->increaseUSN();
			$note->save();
			$transaction->commit();
			return true;
		} 
		catch (StaleObjectError $staleObjectError) {
			Accessory::writeLog('update usn failed');
			sleep(0.3);
			$this->deleteNoteAndAttachments($note, $user_id);
		}
		catch (Exception $e) {
			return false;
		}
	}
}