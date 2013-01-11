<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('note_id')); ?>:</b>
	<?php echo CHtml::encode($data->note_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('uuid')); ?>:</b>
	<?php echo CHtml::encode($data->uuid); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('original_file_name')); ?>:</b>
	<?php echo CHtml::encode($data->original_file_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('phone_size_name')); ?>:</b>
	<?php echo CHtml::encode($data->phone_size_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('large_size_name')); ?>:</b>
	<?php echo CHtml::encode($data->large_size_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('duration')); ?>:</b>
	<?php echo CHtml::encode($data->duration); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('ss_file_name')); ?>:</b>
	<?php echo CHtml::encode($data->ss_file_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('ss_thumb_name')); ?>:</b>
	<?php echo CHtml::encode($data->ss_thumb_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('create_time')); ?>:</b>
	<?php echo CHtml::encode($data->create_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('desc')); ?>:</b>
	<?php echo CHtml::encode($data->desc); ?>
	<br />

	*/ ?>

</div>