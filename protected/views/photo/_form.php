<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'photo-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'note_id'); ?>
		<?php echo $form->textField($model,'note_id',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'note_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uuid'); ?>
		<?php echo $form->textField($model,'uuid',array('size'=>16,'maxlength'=>16)); ?>
		<?php echo $form->error($model,'uuid'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'original_file_name'); ?>
		<?php echo $form->textField($model,'original_file_name',array('size'=>60,'maxlength'=>128)); ?>
		<?php echo $form->error($model,'original_file_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'display_file_name'); ?>
		<?php echo $form->textField($model,'display_file_name',array('size'=>60,'maxlength'=>128)); ?>
		<?php echo $form->error($model,'display_file_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'thumb_name'); ?>
		<?php echo $form->textField($model,'thumb_name',array('size'=>60,'maxlength'=>128)); ?>
		<?php echo $form->error($model,'thumb_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'create_time'); ?>
		<?php echo $form->textField($model,'create_time'); ?>
		<?php echo $form->error($model,'create_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'desc'); ?>
		<?php echo $form->textField($model,'desc',array('size'=>60,'maxlength'=>256)); ?>
		<?php echo $form->error($model,'desc'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'width'); ?>
		<?php echo $form->textField($model,'width'); ?>
		<?php echo $form->error($model,'width'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'height'); ?>
		<?php echo $form->textField($model,'height'); ?>
		<?php echo $form->error($model,'height'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->