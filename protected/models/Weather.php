<?php

/**
 * This is the model class for table "jgg_weather".
 *
 * The followings are the available columns in table 'jgg_weather':
 * @property string $id
 * @property string $note_id
 * @property string $uuid
 * @property integer $code
 * @property string $desc
 * @property integer $temp
 * @property integer $humidity
 * @property integer $pressure
 * @property string $create_time
 *
 * The followings are the available model relations:
 * @property Note $note
 */
class Weather extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Weather the static model class
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
		return 'jgg_weather';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('note_id, uuid, code, create_time', 'required'),
			array('code, temp, humidity, pressure', 'numerical', 'integerOnly'=>true),
			array('note_id', 'length', 'max'=>20),
			array('uuid', 'length', 'max'=>16),
			array('desc', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, note_id, uuid, code, desc, temp, humidity, pressure, create_time', 'safe', 'on'=>'search'),
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
			'code' => 'Code',
			'desc' => 'Desc',
			'temp' => 'Temp',
			'humidity' => 'Humidity',
			'pressure' => 'Pressure',
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
		$criteria->compare('code',$this->code);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('temp',$this->temp);
		$criteria->compare('humidity',$this->humidity);
		$criteria->compare('pressure',$this->pressure);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Try to retrieve weather object from DB for a certain condition
	 * @return boolean value for whether a weather object is exist in the DB
	 */
	public static function isWeatherExist($note_id, $weather_uuid)
	{
		$weather = Weather::model()->findByAttributes(array('note_id'=>$note_id, 'uuid'=>$weather_uuid));
		return ($weather != NULL) ? true : false;
	}
	
}