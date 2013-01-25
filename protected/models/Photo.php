<?php

/**
 * This is the model class for table "jgg_photo".
 *
 * The followings are the available columns in table 'jgg_photo':
 * @property string $id
 * @property string $note_id
 * @property string $uuid
 * @property string $dfs_group_name
 * @property string $original_file_name
 * @property string $dfs_original_file_name
 * @property string $display_file_name
 * @property string $dfs_display_file_name
 * @property string $thumb_name
 * @property string $dfs_thumb_name
 * @property string $create_time
 * @property string $desc
 * @property double $width
 * @property double $height
 * @property integer $orietation
 *
 * The followings are the available model relations:
 * @property Note $note
 */
class Photo extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Photo the static model class
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
		return 'jgg_photo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('note_id, uuid, display_file_name, create_time', 'required'),
			array('orietation', 'numerical', 'integerOnly'=>true),
			array('width, height', 'numerical'),
			array('note_id', 'length', 'max'=>20),
			array('uuid', 'length', 'max'=>16),
			array('dfs_group_name', 'length', 'max'=>45),
			array('original_file_name, dfs_original_file_name, display_file_name, dfs_display_file_name, thumb_name, dfs_thumb_name', 'length', 'max'=>128),
			array('desc', 'length', 'max'=>256),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, note_id, uuid, dfs_group_name, original_file_name, dfs_original_file_name, display_file_name, dfs_display_file_name, thumb_name, dfs_thumb_name, create_time, desc, width, height, orietation', 'safe', 'on'=>'search'),
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
			'dfs_group_name' => 'Dfs Group Name',
			'original_file_name' => 'Original File Name',
			'dfs_original_file_name' => 'Dfs Original File Name',
			'display_file_name' => 'Display File Name',
			'dfs_display_file_name' => 'Dfs Display File Name',
			'thumb_name' => 'Thumb Name',
			'dfs_thumb_name' => 'Dfs Thumb Name',
			'create_time' => 'Create Time',
			'desc' => 'Desc',
			'width' => 'Width',
			'height' => 'Height',
			'orietation' => 'Orietation',
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
		$criteria->compare('dfs_group_name',$this->dfs_group_name,true);
		$criteria->compare('original_file_name',$this->original_file_name,true);
		$criteria->compare('dfs_original_file_name',$this->dfs_original_file_name,true);
		$criteria->compare('display_file_name',$this->display_file_name,true);
		$criteria->compare('dfs_display_file_name',$this->dfs_display_file_name,true);
		$criteria->compare('thumb_name',$this->thumb_name,true);
		$criteria->compare('dfs_thumb_name',$this->dfs_thumb_name,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('width',$this->width);
		$criteria->compare('height',$this->height);
		$criteria->compare('orietation',$this->orietation);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Try to retrieve photo object from DB for a certain condition
	 * @return boolean value for whether a photo object is exist in the DB
	 */
	public static function isPhotoExist($note_id, $photo_uuid)
	{
		$photo = Photo::model()->findByAttributes(array('note_id'=>$note_id, 'uuid'=>$photo_uuid));
		return ($photo != NULL);
	}
}