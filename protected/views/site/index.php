<?php $this->pageTitle=Yii::app()->name; ?>

<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>

<p>Congratulations! You have successfully created your Yii application.</p>


<?php 
	echo "这是测试环境";
	date_default_timezone_set("UTC");
	$script_tz = date_default_timezone_get(); 
	echo "<br><br>current time zone is " . $script_tz; 
	echo "<br>current time is " . date('Y-m-d H:i:s');
?>


<p>For more details on how to further develop this application, please read
the <a href="http://www.yiiframework.com/doc/">documentation</a>.
Feel free to ask in the <a href="http://www.yiiframework.com/forum/">forum</a>,
should you have any questions.</p>