<?php

/**
 * This is the model class for table "jgg_note".
 *
 * The followings are the available columns in table 'jgg_note':
 * @property string $id
 * @property integer $user_id
 * @property string $subject_id
 * @property string $uuid
 * @property integer $usn
 * @property string $content
 * @property string $save_time
 * @property integer $state
 * @property string $last_update_time
 * @property string $weather_id
 * @property string $location_id
 *
 * The followings are the available model relations:
 * @property Audio[] $audios
 * @property Comment[] $comments
 * @property User[] $jggUsers
 * @property Location[] $locations
 * @property User $user
 * @property Subject $subject
 * @property Tag[] $jggTags
 * @property Photo[] $photos
 * @property Video[] $videos
 * @property Weather[] $weathers
 */
class Note extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Note the static model class
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
		return 'jgg_note';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, subject_id, uuid, usn, state', 'required'),
			array('user_id, usn, state', 'numerical', 'integerOnly'=>true),
			array('subject_id, weather_id, location_id', 'length', 'max'=>20),
			array('uuid', 'length', 'max'=>16),
			array('content, save_time, last_update_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, subject_id, uuid, usn, content, save_time, state, last_update_time, weather_id, location_id', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
			'subject' => array(self::BELONGS_TO, 'Subject', 'subject_id'),
			'photos' => array(self::HAS_MANY, 'Photo', 'note_id'),
			'videos' => array(self::HAS_MANY, 'Video', 'note_id'),
			'audios' => array(self::HAS_MANY, 'Audio', 'note_id'),
			'tags' => array(self::MANY_MANY, 'Tag', 'jgg_note_tag(note_id, tag_id)'),
			'locations' => array(self::HAS_MANY, 'Location', 'note_id'),
			'weathers' => array(self::HAS_MANY, 'Weather', 'note_id'),
			'comments' => array(self::HAS_MANY, 'Comment', 'note_id'),
			'claques' => array(self::MANY_MANY, 'User', 'jgg_favorite(note_id, user_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'subject_id' => 'Subject',
			'uuid' => 'UUID',
			'usn' => 'USN',
			'content' => 'Content',
			'save_time' => 'Save Time',
			'state' => 'State',
			'last_update_time' => 'Last Update Time',
			'weather_id' => 'Weather',
			'location_id' => 'Location',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('subject_id',$this->subject_id,true);
		$criteria->compare('uuid',$this->uuid,true);
		$criteria->compare('usn',$this->usn);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('save_time',$this->save_time,true);
		$criteria->compare('state',$this->state);
		$criteria->compare('last_update_time',$this->last_update_time,true);
		$criteria->compare('weather_id',$this->weather_id,true);
		$criteria->compare('location_id',$this->location_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * 	Retrieves Note id by its user_id and uuid
	 * 	@return note id
	 */
	public static function fetchNoteId($user_id, $note_uuid)
	{
		$note = Note::model()->findByAttributes(array('user_id'=>$user_id, 'uuid'=>$note_uuid));
		return ($note != NULL) ? $note->id : NULL;
	}
	
	/**
	 * 	Retrieves subject id for a note
	 * 	@return subjectId
	 */
	public static function fetchSubjectId($note_id)
	{
		$note = Note::model()->findByPk($note_id);
		return ($note != NULL) ? $note->subject_id : NULL;
	}
	
	/**
	 * 	Retrieves subject id for a note
	 * 	@return subjectId
	 */
	public static function fetchSubjectUUID($note_id)
	{
		$subject_id = Note::fetchSubjectId($note_id);
		$subject = Subject::model()->findByPk($subject_id);
		return $subject->uuid;
	}
	
	// return bool value for whether user is exist for specific user Id
	public static function isNoteExist($note_id)
	{
		if (Note::model()->findByPk($note_id))
			return true;
		else
			return false;
	}
}