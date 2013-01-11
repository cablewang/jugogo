<?php

/**
 * This is the model class for table "jgg_avatar".
 *
 * The followings are the available columns in table 'jgg_avatar':
 * @property string $id
 * @property string $subject_id
 * @property string $uuid
 * @property string $avatar_name
 * @property string $avatar_thumb_name
 * @property string $create_time
 * @property string $last_update_time
 *
 * The followings are the available model relations:
 * @property Subject $subject
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
			array('subject_id, uuid, avatar_name, create_time', 'required'),
			array('subject_id', 'length', 'max'=>20),
			array('uuid', 'length', 'max'=>16),
			array('avatar_name, avatar_thumb_name', 'length', 'max'=>128),
			array('last_update_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, subject_id, uuid, avatar_name, avatar_thumb_name, create_time, last_update_time', 'safe', 'on'=>'search'),
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
			'uuid' => 'Uuid',
			'avatar_name' => 'Avatar Name',
			'avatar_thumb_name' => 'Avatar Thumb Name',
			'create_time' => 'Create Time',
			'last_update_time' => 'Last Update Time',
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
		$criteria->compare('avatar_name',$this->avatar_name,true);
		$criteria->compare('avatar_thumb_name',$this->avatar_thumb_name,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('last_update_time',$this->last_update_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}