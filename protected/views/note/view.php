<?php
$this->breadcrumbs=array(
	'Notes'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Note', 'url'=>array('index')),
	array('label'=>'Create Note', 'url'=>array('create')),
	array('label'=>'Update Note', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Note', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Note', 'url'=>array('admin')),
);
?>

<h1>View Note #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'user_id',
		'subject_id',
		'uuid',
		'content',
		'save_time',
		'state',
		'last_update_time',
	),
)); ?>
