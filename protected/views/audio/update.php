<?php
$this->breadcrumbs=array(
	'Audios'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Audio', 'url'=>array('index')),
	array('label'=>'Create Audio', 'url'=>array('create')),
	array('label'=>'View Audio', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Audio', 'url'=>array('admin')),
);
?>

<h1>Update Audio <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>