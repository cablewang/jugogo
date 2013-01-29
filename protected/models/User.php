<?php

/**
 * This is the model class for table "jgg_user".
 *
 * The followings are the available columns in table 'jgg_user':
 * @property integer $id
 * @property string $password
 * @property string $email
 * @property integer $state
 * @property string $state_change_reason
 * @property string $state_change_time
 * @property string $create_time
 * @property string $reg_time
 * @property string $last_login_time
 * @property integer $score
 * @property integer $storage_size
 * @property integer $storage_usage
 * @property string $last_update_time
 * @property integer $update_count
 * @property string $full_sync_before
 *
 * The followings are the available model relations:
 * @property Comment[] $comments
 * @property Comment[] $comments1
 * @property Eventvalidate[] $eventvalidates
 * @property Note[] $jggNotes
 * @property FriendGroup[] $friendGroups
 * @property Friendship[] $friendships
 * @property Friendship[] $friendships1
 * @property Invitation[] $invitations
 * @property Message[] $messages
 * @property Message[] $messages1
 * @property Note[] $notes
 * @property RelationRequest[] $relationRequests
 * @property RelationRequest[] $relationRequests1
 * @property Subject[] $jggSubjects
 * @property Tag[] $jggTags
 */
class User extends OptimistLockingActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
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
		return 'jgg_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('password, state, create_time', 'required'),
			array('state, score, storage_size, storage_usage, update_count', 'numerical', 'integerOnly'=>true),
			array('password', 'length', 'max'=>32),
			array('email', 'length', 'max'=>128),
			array('state_change_reason', 'length', 'max'=>256),
			array('state_change_time, reg_time, last_login_time, last_update_time, full_sync_before', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, password, email, state, state_change_reason, state_change_time, create_time, reg_time, last_login_time, score, storage_size, storage_usage, last_update_time, update_count, full_sync_before', 'safe', 'on'=>'search'),
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
			'wroteComments' => array(self::HAS_MANY, 'Comment', 'author_id'),
			'receivedComments' => array(self::HAS_MANY, 'Comment', 'receiver_id'),
			'eventvalidates' => array(self::HAS_MANY, 'Eventvalidate', 'user_id'),
			'favoriteNotes' => array(self::MANY_MANY, 'Note', 'jgg_favorite(user_id, note_id)'),
			'friendGroups' => array(self::HAS_MANY, 'FriendGroup', 'user_id'),
			'ownFriendships' => array(self::HAS_MANY, 'Friendship', 'owner_id'),
			'inFriendships' => array(self::HAS_MANY, 'Friendship', 'friend_id'),
			'invitations' => array(self::HAS_MANY, 'Invitation', 'sender_id'),
			'sentMessages' => array(self::HAS_MANY, 'Message', 'author_id'),
			'receivedMessages' => array(self::HAS_MANY, 'Message', 'receiver_id'),
			'notes' => array(self::HAS_MANY, 'Note', 'user_id'),
			'sentRelationRequests' => array(self::HAS_MANY, 'RelationRequest', 'requester'),
			'receivedRelationRequests' => array(self::HAS_MANY, 'RelationRequest', 'receiver'),
			'subjects' => array(self::MANY_MANY, 'Subject', 'jgg_user_subject(user_id, subject_id)'),
			'tags' => array(self::MANY_MANY, 'Tag', 'jgg_user_tag(user_id, tag_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'password' => 'Password',
			'email' => 'Email',
			'state' => 'State',
			'state_change_reason' => 'State Change Reason',
			'state_change_time' => 'State Change Time',
			'create_time' => 'Create Time',
			'reg_time' => 'Register Time',
			'last_login_time' => 'Last Login Time',
			'score' => 'Score',
			'storage_size' => 'Storage Size',
			'storage_usage' => 'Storage Usage',
			'last_update_time' => 'Last Update Time',
			'update_count' => 'Update Count',
			'full_sync_before' => 'Full Sync Before',
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
		$criteria->compare('password',$this->password,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('state',$this->state);
		$criteria->compare('state_change_reason',$this->state_change_reason,true);
		$criteria->compare('state_change_time',$this->state_change_time,true);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('reg_time',$this->reg_time,true);
		$criteria->compare('last_login_time',$this->last_login_time,true);
		$criteria->compare('score',$this->score);
		$criteria->compare('storage_size',$this->storage_size);
		$criteria->compare('storage_usage',$this->storage_usage);
		$criteria->compare('last_update_time',$this->last_update_time,true);
		$criteria->compare('update_count',$this->update_count);
		$criteria->compare('full_sync_before',$this->full_sync_before,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	// return bool value for whether user is exist for specific user Id
	public static function isUserExist($user_id)
	{
		if (User::model()->findByPk($user_id))
			return true;
		else
			return false;
	}
	
	public function getlockingAttribute()
	{
		return 'update_count';
	}
}