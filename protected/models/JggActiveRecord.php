<?php
abstract class JggActiveRecord extends CActiveRecord
{
	
	/**
	 * Prepares create_time, create_user_id, update_time and update_user_id attributes before performing validation
	 */
	protected function beforeValidate()
	{
		if ($this->isNewRecord)
		{
			// set the create date, last updated date and the user doing the creating
			date_default_timezone_set('UTC');
			$this->create_time = date('Y-m-d H:i:s');
		} else {
			// not a new record, so just set the last updated time and last updated user id
			date_default_timezone_set('UTC');
			$this->last_update_time = date('Y-m-d H:i:s');
		}
		
		return parent::beforeValidate();
	}
}