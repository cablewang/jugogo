<?php
$this->breadcrumbs=array(
	'Audios'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Audio', 'url'=>array('index')),
	array('label'=>'Create Audio', 'url'=>array('create')),
	array('label'=>'Update Audio', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Audio', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Audio', 'url'=>array('admin')),
);
?>

<h1>View Audio #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'note_id',
		'uuid',
		'original_file_name',
		'mp3_file_name',
		'duration',
		'create_time',
		'desc',
	),
)); ?>
