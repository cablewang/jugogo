<?php
/**
 * 错误处理句柄方法
 * @param int $errno 必需:错误报告级别,为用户定义的错误规定错误报告级别
 * @param string $errstr 必需:为用户定义的错误规定错误消息
 * @param string $errfile 可选：错误文件名
 */
function myErrorHandler($errno, $errstr, $errfile = '',$errorline='') {
	$logfile =APP_ROOT . '/log/errors_' . date ( 'Y-m-d' ) . '.log';
	if (! file_exists ( $logfile )) {
		if (touch ( $logfile )) {
			@chmod ( $logfile, 0777 );
		}
	}
	//错误类型描述数组
	$errortype = array (E_ERROR => "Error", E_WARNING => "Warning", E_PARSE => "Parsing Error", E_NOTICE => "Notice", E_CORE_ERROR => "Core Error", E_CORE_WARNING => "Core Warning", E_COMPILE_ERROR => "Compile Error", E_COMPILE_WARNING => "Compile Warning", E_USER_ERROR => "User Error", E_USER_WARNING => "User Warning", E_USER_NOTICE => "User Notice", E_STRICT => "Runtime Notice" );
	//需要记录日志的错误类型，可根据需要减少
	$save_errors = array (E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR );
	//if (in_array ( $errno, $save_errors )) {
	error_log ( date ( 'Y-m-d H:i:s' ) . "||$errortype[$errno]||file:$errfile,line:$errorline||$errstr||" . PHP_EOL, 3, $logfile );
	//}
}

/**
 * 异常处理句柄方法
 * 如果系统有未捕获的异常时会执行myExceptionHandler方法
 * @param Exception $errno 异常对象
 */
function myExceptionHandler($myexception) {
	//记录错误日志
	trigger_error ( 'EXCEPTION:' . $myexception->getMessage (), E_ERROR );
	//显示失败页面，可以自定义错误页面
	echo $myexception->getMessage ();exit;
}
// 帮助类，服务范围——全局
// 功能方法：UUID生成，压缩UUID，解压UUID，发送email，发送错误报告给管理员，包装和发送REST数据包，解析HTTP状态代码
//
class Accessory
{
	/**
	* 记录日志
	* @param mix $errstr 数组或字符串
	* @param unknown_type $pre　日子文件前缀，默认为log
	*/
	public static function writeLog($logstr, $pre='log') {
		$logfile =APP_ROOT . '/log/'.$pre.'_' . date ( 'Y-m-d' ) . '.log';
		if (! file_exists ( $logfile )) {
			if(touch($logfile)){
				@chmod ( $logfile, 0777 );
			}
		}
		// define script name
		$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
		if (is_array($logstr)) {
			$logstr["script_name"] = $script_name;
		} else {
			$logstr = '(' . $script_name . ') ' . $logstr;
		}
		error_log ( date ( 'Y-m-d H:i:s' ) .':'.(is_array($logstr) ? json_encode($logstr) : $logstr) .PHP_EOL, 3, $logfile );
	}
	
	public static function gen_uuid() {
		// with '-' '%04x%04x-%04x-%04x-%04x-%04x%04x%04x'
		return sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
				// 16 bits for "time_mid"
				mt_rand( 0, 0xffff ),
	
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand( 0, 0x0fff ) | 0x4000,
	
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand( 0, 0x3fff ) | 0x8000,
	
				// 48 bits for "node"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	/**
	 * 压缩32个字符的UUID字符串成为16"字"的二进制字符串
	 * @param string $uuid 32字符的UUID字符串
	 * @return string 16"字"二进制字符串
	 */
	public static function packUUID($uuid)
	{
		if ($uuid !== null) {
			$dbValue = pack('H*', strtoupper($uuid));
			return $dbValue;
		}
	}
	
	/**
	 * 解压缩16"字"二进制字符串成为32字符的UUID字符串
	 * @param string $dbValue 16"字"二进制字符串
	 * @return string 32字符的UUID字符串
	 */
	public static function unpackUUID($dbValue)
	{
		if ($dbValue !== null) {
			$uuid = implode('', unpack('H*', $dbValue));
			return strtoupper($uuid);
		}
	}
	
	// return a md5 string for the input string
	public static function md5Encrypt($value)
	{
		return md5($value);
	}
	
	/**
	 * 一个简单的纯文本email发送方法
	 * @param string $toAddress 目标email地址，$toName 目标人名或称谓，
	 * 				$fromAddress 发送者email地址，$fromName 发送者人名或称谓， $subject email标题
	 * 				$bodyText email的文字内容
	 */
	public static function sendEmail($toAddress, $toName, $fromAddress, $fromName, $subject, $bodyText='', $bodyHTML)
	{
		// 调用Zend的mail扩展
		Yii::import('application.vendors.*');
		require "Zend/Mail.php";
	
		$mail = new Zend_Mail('utf-8');
		$mail->setHeaderEncoding(Zend_Mime::ENCODING_QUOTEDPRINTABLE);
		$mail->addTo($toAddress, $toName);
		$mail->setFrom($fromAddress, $fromName);
		$mail->setSubject($subject);
		$mail->setBodyText($bodyText);
		$mail->setBodyHtml($bodyHTML);
		$mail->send();
	}
	
	public static function sendErrorMessageToAdmin($error_code, $message, $error_source, $location='')
	{
		// write content before v 2.0 release
	}
	
	/**
	 * 向客户端返回错误响应
	 * @param string $statusCode 状态代码，$message 错误信息.
	 */ 
	public static function warningResponse($statusCode, $message, $syncStatusCode='')
	{
		$response = array(
				'status_code' => $statusCode,
				'sync_status_code' => $syncStatusCode,
				'error_message' => $message,
		);
		Accessory::sendRESTResponse(500, CJSON::encode($response));
	}
	
	/**
	 * 根据错误代码返回对应的错误信息
	 * @param string $errorCode 错误代码
	 */
	public static function errorMessageForErrorCode($errorCode)
	{
		
	}
	
	/**
	 * 向客户端发现REST API响应信息
	 * @param string $statusCode API状态代码，$body JSON封装的响应信息体。
	 */
	public static function sendRESTResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		// set the status
		$status_header = 'HTTP/1.1' . $status . ' ' . Accessory::getStatusCodeMessage($status);
		header($status_header);
		// and the content type
		header('Content-type: ' . $content_type);
	
		echo $body;
		Yii::app()->end();
	}
	
	/**
	 * 返回HTTP状态代码对应的状态信息
	 * @param integer $status HTTP状态代码
	 */
	public static function getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
				200 => 'OK',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}
	
	/**
	 * 返回REST API状态代码对应的状态信息
	 * @param integer $status HTTP状态代码
	 */
	public static function getRESTStatusMessage($statusCode){
		$statusMessages = Array(
				101 => 'RESPONSE_STATUS_PARAM_INVALID',
				102 => 'RESPONSE_STATUS_FILE_UPLOAD_FAILED',
				104 => 'RESPONSE_STATUS_WRONG_METHOD',
				300 => 'SYNC_STATUS_TASK_DONE_BEFORE',
		);
		return (isset($statusMessages[$statusCode])) ? $statusMessages[$statusCode] : '';
	}
	
	private static function sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		// set the status
		$status_header = 'HTTP/1.1' . $status . ' ' . $this->_getStatusCodeMessage($status);
		header($status_header);
		// and the content type
		header('Content-type: ' . $content_type);
	
		// pages with body are easy
		if ($body != '') {
			// send the body
			echo $body;
		} else { // we need to create the body if none is passed
			//create some body message
			$message = '';
				
			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}
				
			// servers don't always have a signature turned on
			// (this is an apache directive "ServerSignature On")
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . 'Server at' . $_SERVER['SERVER_NAME'] . ' Post ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
			// this should be templated in a real-world solution
			$body = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
			</head>
			<body>
			<h1>' . $this->_getStatusCodeMessage($status) . '</h1>
			<p>' . $message . '</p>
			<hr />
			<address>' . $signature . '</address>
			</body>
			</html>';
			 
			echo $body;
		}
		Yii::app()->end();
	}
}