<?php
$this->breadcrumbs=array(
	'Audios'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Audio', 'url'=>array('index')),
	array('label'=>'Manage Audio', 'url'=>array('admin')),
);
?>

<h1>Create Audio</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>