<?php

/**
 * This is the model class for table "jgg_avatar".
 *
 * The followings are the available columns in table 'jgg_avatar':
 * @property string $id
 * @property string $subject_id
 * @property string $uuid
 * @property integer $usn
 * @property string $dfs_group_name
 * @property string $avatar_name
 * @property string $dfs_avatar_name
 * @property string $avatar_thumb_name
 * @property string $dfs_avatar_thumb_name
 * @property string $create_time
 *
 * The followings are the available model relations:
 * @property Subject $subject
 * @property Subject[] $subjects
 */
class Avatar extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Avatar the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'jgg_avatar';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('subject_id, uuid, usn, avatar_name, create_time', 'required'),
			array('usn', 'numerical', 'integerOnly'=>true),
			array('subject_id', 'length', 'max'=>20),
			array('uuid', 'length', 'max'=>16),
			array('dfs_group_name', 'length', 'max'=>45),
			array('avatar_name, dfs_avatar_name, avatar_thumb_name, dfs_avatar_thumb_name', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, subject_id, uuid, usn, dfs_group_name, avatar_name, dfs_avatar_name, avatar_thumb_name, dfs_avatar_thumb_name, create_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'subject' => array(self::BELONGS_TO, 'Subject', 'subject_id'),
			'notInUse' => array(self::HAS_MANY, 'Subject', 'current_avatar_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'subject_id' => 'Subject',
			'uuid' => 'UUID',
			'usn' => 'USN',
			'dfs_group_name' => 'DFS Group Name',
			'avatar_name' => 'Avatar Name',
			'dfs_avatar_name' => 'DFS Avatar Name',
			'avatar_thumb_name' => 'Avatar Thumb Name',
			'dfs_avatar_thumb_name' => 'DFS Avatar Thumb Name',
			'create_time' => 'Create Time',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('subject_id',$this->subject_id,true);
		$criteria->compare('uuid',$this->uuid,true);
		$criteria->compare('usn',$this->usn);
		$criteria->compare('dfs_group_name',$this->dfs_group_name,true);
		$criteria->compare('avatar_name',$this->avatar_name,true);
		$criteria->compare('dfs_avatar_name',$this->dfs_avatar_name,true);
		$criteria->compare('avatar_thumb_name',$this->avatar_thumb_name,true);
		$criteria->compare('dfs_avatar_thumb_name',$this->dfs_avatar_thumb_name,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	// return bool value for whether avatar is exist for specific subject id and avatar uuid
	public static function isAvatarExist($subject_id, $avatar_uuid)
	{
		if (Avatar::model()->findByAttributes(array('subject_id'=>$subject_id, 'uuid'=>$avatar_uuid)))
			return true;
		else
			return false;
	}
}