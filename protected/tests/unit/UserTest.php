<?php
class UserTest extends CDbTestCase
{
	public $email = 'cable@google.com';
	
	public function testCreate()
	{
		$user = new User();
		$user->setAttributes(array(
			'password' => md5('doudou12'),
			'email' => 'cable@google.com',
			'state' => 1,
			'create_time' => new CDbExpression('NOW()'),
		));
		$this->assertTrue($user->save());
		$this->assertEquals($user->email, 'cable@google.com');
	}
	
	public function testUpdate()
	{
		$user = User::model()->find('email=:currentEmail', array(':currentEmail'=>$this->email));
		$user->state = 2;
		$this->assertTrue($user->save());
		$this->assertEquals($user->state, 2);
	}
	
	public function testDelete()
	{
		$user = User::model()->find('email=:currentEmail', array(':currentEmail'=>$this->email));
		$this->assertTrue($user->delete());
		$deleteduser = User::model()->find('email=:currentEmail', array(':currentEmail'=>$this->email));
		$this->assertEquals(NULL, $deleteduser);
	}
}