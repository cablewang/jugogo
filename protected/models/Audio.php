<?php

/**
 * This is the model class for table "jgg_audio".
 *
 * The followings are the available columns in table 'jgg_audio':
 * @property string $id
 * @property string $note_id
 * @property string $uuid
 * @property string $dfs_group_name
 * @property string $original_file_name
 * @property string $dfs_original_file_name
 * @property string $mp3_file_name
 * @property string $dfs_mp3_file_name
 * @property double $duration
 * @property string $create_time
 * @property string $desc
 *
 * The followings are the available model relations:
 * @property Note $note
 */
class Audio extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Audio the static model class
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
		return 'jgg_audio';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('note_id, uuid, original_file_name, duration, create_time', 'required'),
			array('duration', 'numerical'),
			array('note_id', 'length', 'max'=>20),
			array('uuid', 'length', 'max'=>16),
			array('dfs_group_name, dfs_mp3_file_name', 'length', 'max'=>45),
			array('original_file_name, dfs_original_file_name, mp3_file_name', 'length', 'max'=>128),
			array('desc', 'length', 'max'=>256),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, note_id, uuid, dfs_group_name, original_file_name, dfs_original_file_name, mp3_file_name, dfs_mp3_file_name, duration, create_time, desc', 'safe', 'on'=>'search'),
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
			'mp3_file_name' => 'Mp3 File Name',
			'dfs_mp3_file_name' => 'Dfs Mp3 File Name',
			'duration' => 'Duration',
			'create_time' => 'Create Time',
			'desc' => 'Desc',
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
		$criteria->compare('mp3_file_name',$this->mp3_file_name,true);
		$criteria->compare('dfs_mp3_file_name',$this->dfs_mp3_file_name,true);
		$criteria->compare('duration',$this->duration);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('desc',$this->desc,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	 * Try to retrieve audio object from DB for a certain condition
	 * @return boolean value for whether an audio object is exist in the DB
	 */
	public static function isAudioExist($note_id, $audio_uuid)
	{
		$audio = Audio::model()->findByAttributes(array('note_id'=>$note_id, 'uuid'=>$audio_uuid));
		return ($audio != NULL) ? true : false;
	}
	
}