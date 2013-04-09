<?php

/**
 * This is the model class for table "jgg_tag".
 *
 * The followings are the available columns in table 'jgg_tag':
 * @property integer $id
 * @property string $name
 * @property string $image_name
 * @property string $thumb_name
 * @property integer $order
 * @property string $select_times
 * @property integer $deleted
 *
 * The followings are the available model relations:
 * @property Note[] $jggNotes
 * @property User[] $jggUsers
 */
class Tag extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tag the static model class
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
		return 'jgg_tag';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, image_name, thumb_name, order, select_times', 'required'),
			array('order, deleted', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>16),
			array('image_name, thumb_name', 'length', 'max'=>128),
			array('select_times', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, image_name, thumb_name, order, select_times, deleted', 'safe', 'on'=>'search'),
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
			'notes' => array(self::MANY_MANY, 'Note', 'jgg_note_tag(tag_id, note_id)'),
			'users' => array(self::MANY_MANY, 'User', 'jgg_user_tag(tag_id, user_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'image_name' => 'Image Name',
			'thumb_name' => 'Thumb Name',
			'order' => 'Order',
			'select_times' => 'Select Times',
			'deleted' => 'Deleted',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('image_name',$this->image_name,true);
		$criteria->compare('thumb_name',$this->thumb_name,true);
		$criteria->compare('order',$this->order);
		$criteria->compare('select_times',$this->select_times,true);
		$criteria->compare('deleted',$this->deleted);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}