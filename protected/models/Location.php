<?php

/**
 * This is the model class for table "jgg_location".
 *
 * The followings are the available columns in table 'jgg_location':
 * @property string $id
 * @property string $note_id
 * @property string $uuid
 * @property string $name
 * @property double $longitude
 * @property double $latitude
 * @property string $create_time
 *
 * The followings are the available model relations:
 * @property Note $note
 */
class Location extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Location the static model class
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
		return 'jgg_location';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('note_id, uuid, name, longitude, latitude, create_time', 'required'),
			array('longitude, latitude', 'numerical'),
			array('note_id', 'length', 'max'=>20),
			array('uuid', 'length', 'max'=>16),
			array('name', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, note_id, uuid, name, longitude, latitude, create_time', 'safe', 'on'=>'search'),
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
			'note' => array(self::BELONGS_TO, 'Note', 'note_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'note_id' => 'Note',
			'uuid' => 'Uuid',
			'name' => 'Name',
			'longitude' => 'Longitude',
			'latitude' => 'Latitude',
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
		$criteria->compare('note_id',$this->note_id,true);
		$criteria->compare('uuid',$this->uuid,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('longitude',$this->longitude);
		$criteria->compare('latitude',$this->latitude);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Try to retrieve location object from DB for a certain condition
	 * @return boolean value for whether a location object is exist in the DB
	 */
	public static function isLocationExist($note_id, $location_uuid)
	{
		$location = Location::model()->findByAttributes(array('note_id'=>$note_id, 'uuid'=>$location_uuid));
		return ($location != NULL) ? true : false;
	}
	
}