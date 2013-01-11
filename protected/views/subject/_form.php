<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'subject-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'user_id'); ?>
		<?php echo $form->textField($model,'user_id'); ?>
		<?php echo $form->error($model,'user_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uuid'); ?>
		<?php echo $form->textField($model,'uuid',array('size'=>16,'maxlength'=>16)); ?>
		<?php echo $form->error($model,'uuid'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'display_name'); ?>
		<?php echo $form->textField($model,'display_name',array('size'=>32,'maxlength'=>32)); ?>
		<?php echo $form->error($model,'display_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'avatar_name'); ?>
		<?php echo $form->textField($model,'avatar_name',array('size'=>60,'maxlength'=>128)); ?>
		<?php echo $form->error($model,'avatar_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'avatar_thumb_name'); ?>
		<?php echo $form->textField($model,'avatar_thumb_name',array('size'=>60,'maxlength'=>128)); ?>
		<?php echo $form->error($model,'avatar_thumb_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'birthday'); ?>
		<?php echo $form->textField($model,'birthday'); ?>
		<?php echo $form->error($model,'birthday'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'gender'); ?>
		<?php echo $form->textField($model,'gender'); ?>
		<?php echo $form->error($model,'gender'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'introduction'); ?>
		<?php echo $form->textField($model,'introduction',array('size'=>60,'maxlength'=>256)); ?>
		<?php echo $form->error($model,'introduction'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'type'); ?>
		<?php echo $form->textField($model,'type'); ?>
		<?php echo $form->error($model,'type'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'create_time'); ?>
		<?php echo $form->textField($model,'create_time'); ?>
		<?php echo $form->error($model,'create_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'last_update_time'); ?>
		<?php echo $form->textField($model,'last_update_time'); ?>
		<?php echo $form->error($model,'last_update_time'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->