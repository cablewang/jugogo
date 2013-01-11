<?php
$this->breadcrumbs=array(
	'Audios',
);

$this->menu=array(
	array('label'=>'Create Audio', 'url'=>array('create')),
	array('label'=>'Manage Audio', 'url'=>array('admin')),
);
?>

<h1>Audios</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
