<div class="families form">
<?php echo $this->Form->create('Family'); ?>
	<fieldset>
		<legend><?php echo __('Add Family'); ?></legend>
	<?php
		echo $this->Form->input('parent_id');
		echo $this->Form->input('lft');
		echo $this->Form->input('rght');
		echo $this->Form->input('name');
		echo $this->Form->input('gender');
		echo $this->Form->input('age');
		echo $this->Form->input('married');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Families'), array('action' => 'index')); ?></li>
	</ul>
</div>
