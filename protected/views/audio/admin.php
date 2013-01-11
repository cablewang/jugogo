<?php
$this->breadcrumbs=array(
	'Audios'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List Audio', 'url'=>array('index')),
	array('label'=>'Create Audio', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('audio-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Audios</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'audio-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'note_id',
		'uuid',
		'original_file_name',
		'mp3_file_name',
		'duration',
		/*
		'create_time',
		'desc',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
