<?php

/**
 * This is the model class for table "jgg_subject".
 *
 * The followings are the available columns in table 'jgg_subject':
 * @property string $id
 * @property string $uuid
 * @property integer $usn
 * @property string $current_avatar_id
 * @property string $display_name
 * @property string $birthday
 * @property integer $gender
 * @property string $introduction
 * @property integer $type
 * @property string $create_time
 * @property string $last_update_time
 * @property integer $privacy
 * @property string $uurn
 *
 * The followings are the available model relations:
 * @property Avatar[] $avatars
 * @property Note[] $notes
 * @property Avatar $currentAvatar
 * @property User[] $jggUsers
 */
class Subject extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Subject the static model class
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
		return 'jgg_subject';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('uuid, usn, type, create_time, uurn', 'required'),
			array('usn, gender, type, privacy', 'numerical', 'integerOnly'=>true),
			array('uuid', 'length', 'max'=>16),
			array('current_avatar_id', 'length', 'max'=>20),
			array('display_name', 'length', 'max'=>32),
			array('introduction', 'length', 'max'=>256),
			array('uurn', 'length', 'max'=>64),
			array('birthday, last_update_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, uuid, usn, current_avatar_id, display_name, birthday, gender, introduction, type, create_time, last_update_time, privacy, uurn', 'safe', 'on'=>'search'),
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
			'avatars' => array(self::HAS_MANY, 'Avatar', 'subject_id'),
			'notes' => array(self::HAS_MANY, 'Note', 'subject_id'),
			'currentAvatar' => array(self::BELONGS_TO, 'Avatar', 'current_avatar_id'),
			'users' => array(self::MANY_MANY, 'User', 'jgg_user_subject(subject_id, user_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'uuid' => 'UUID',
			'usn' => 'USN',
			'current_avatar_id' => 'Current Avatar',
			'display_name' => 'Display Name',
			'birthday' => 'Birthday',
			'gender' => 'Gender',
			'introduction' => 'Introduction',
			'type' => 'Type',
			'create_time' => 'Create Time',
			'last_update_time' => 'Last Update Time',
			'privacy' => 'Privacy',
			'uurn' => 'UURN',
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
		$criteria->compare('uuid',$this->uuid,true);
		$criteria->compare('usn',$this->usn);
		$criteria->compare('current_avatar_id',$this->current_avatar_id,true);
		$criteria->compare('display_name',$this->display_name,true);
		$criteria->compare('birthday',$this->birthday,true);
		$criteria->compare('gender',$this->gender);
		$criteria->compare('introduction',$this->introduction,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('last_update_time',$this->last_update_time,true);
		$criteria->compare('privacy',$this->privacy);
		$criteria->compare('uurn',$this->uurn,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	// return bool value for whether any subject exist with specific user Id and UUID
	public static function checkSubjectWithUserIDAndUUID($user_id, $uuid)
	{
		$subject = Subject::fetchSubject($user_id, $uuid);
		return ($subject !== NULL);
	}

	// return bool value for whether any subject exist with specific user Id and UUID
	public static function isSubjectExist($subject_id)
	{
		$subject = Subject::model()->findByPk($subject_id);
		return ($subject !== NULL);
	}
	
	// return subject for particular user Id and UUID, return NULL when not found
	public static function fetchSubject($user_id, $uuid)
	{
		$result = NULL;
		$user = User::model()->findByPk($user_id);
		$subjects = $user->subjects;
		if (count($subjects) > 0) {
			foreach ($subjects as $subject) {
				if ($subject->uuid == $uuid) {
					$result = $subject;
				}
			}
		}
		return $result;
	}
	
	// return subject id of a certain subject with specific user Id and UUID
	public static function fetchSubjectId($user_id, $uuid)
	{
		$subject = Subject::fetchSubject($user_id, $uuid);
		return $subject != NULL ? $subject->id : NULL;
	}
	
	// return all subjects related to particular user
	public static function fetchAllSubjects($user_id)
	{
		$sql = "SELECT us.subject_id id, role, uuid, display_name,
						birthday, gender, introduction, type,
						create_time, last_update_time, privacy, uurn
				FROM jgg_user_subject us, jgg_subject s
				WHERE us.user_id =:userId AND us.subject_id = s.id";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":userId", $user->id, PDO::PARAM_INT);
		return $command->execute();
	}
	
	/**
	 * associate specified user to current project
	 */
	public function associateUserToSubject($user, $role)
	{
		$sql = "INSERT INTO jgg_user_subject (user_id, subject_id, role) VALUES (:userId, :subjectId, :role)";
		$command = Yii::app()->db->createCommand($sql);
		$command->bindValue(":userId", $user->id, PDO::PARAM_INT);
		$command->bindValue(":subjectId", $this->id, PDO::PARAM_INT);
		$command->bindValue(":role", $role, PDO::PARAM_INT);
		return $command->execute();
	}
	
}