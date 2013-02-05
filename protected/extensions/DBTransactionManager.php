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
			$this->saveObjectWithUSN($object, $user_id);
		}
		catch (Exception $e) {
			return null;
		}
		
	}
}