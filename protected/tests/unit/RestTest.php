<?php
class RestTest extends CDbTestCase
{
	private $myuid = '550e8400e29b41d4a716446655440000';
	
	public function testAuthkey()
	{
		$hexString = pack("h*", $this->myuid);
		$unpackChars = unpack('h*', $hexString);
		echo $hexString . ' ' . implode('', $unpackChars) . '\n';
		foreach ($unpackChars as $char) {
			echo $char;
		} 
	}
}