<?php

/**
 * This is the model class for table "jgg_actionauth".
 *
 * The followings are the available columns in table 'jgg_actionauth':
 * @property string $id
 * @property integer $action_type
 * @property integer $client_id
 * @property string $client_sub_id
 * @property string $key
 * @property string $create_time
 */
class Actionauth extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Actionauth the static model class
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
		return 'jgg_actionauth';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('action_type, client_id', 'numerical', 'integerOnly'=>true),
			array('client_sub_id, key', 'length', 'max'=>16),
			array('create_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, action_type, client_id, client_sub_id, key, create_time', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'action_type' => 'Action Type',
			'client_id' => 'Client',
			'client_sub_id' => 'Client Sub',
			'key' => 'Key',
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
		$criteria->compare('action_type',$this->action_type);
		$criteria->compare('client_id',$this->client_id);
		$criteria->compare('client_sub_id',$this->client_sub_id,true);
		$criteria->compare('key',$this->key,true);
		$criteria->compare('create_time',$this->create_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}