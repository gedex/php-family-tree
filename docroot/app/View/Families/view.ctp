<div class="families view">
<h2><?php  echo __('Family'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($family['Family']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Parent Id'); ?></dt>
		<dd>
			<?php echo h($family['Family']['parent_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Lft'); ?></dt>
		<dd>
			<?php echo h($family['Family']['lft']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Rght'); ?></dt>
		<dd>
			<?php echo h($family['Family']['rght']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($family['Family']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Gender'); ?></dt>
		<dd>
			<?php echo h($family['Family']['gender']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Age'); ?></dt>
		<dd>
			<?php echo h($family['Family']['age']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Married'); ?></dt>
		<dd>
			<?php echo h($family['Family']['married']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Family'), array('action' => 'edit', $family['Family']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Family'), array('action' => 'delete', $family['Family']['id']), null, __('Are you sure you want to delete # %s?', $family['Family']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Families'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Family'), array('action' => 'add')); ?> </li>
	</ul>
</div>
