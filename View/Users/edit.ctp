<div class="users form">
<?php echo $this->Form->create('User'); ?>
	<fieldset>
		<legend><?php echo __d('amanager', 'Edit User'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('username', array('label'=>__d('amanager', 'Username')));
		echo $this->Form->input('password', array('label'=>__d('amanager', 'password'), 'value'=>'', 'required'=> false));
		echo $this->Form->input('password2', array('label'=>__d('amanager', 'Confirm password'),'type'=>'password', 'value'=>'', 'required'=> false));
		echo $this->Form->input('email', array('label'=>__d('amanager', 'Email')));
		echo $this->Form->input('passwordchangecode', array('label'=>__d('amanager', 'Password change code')));
		echo $this->Form->input('status');
		echo $this->Form->input('Group', array('label'=>__d('amanager', 'Group')));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Form->postLink('<i class="icon-white icon-trash"></i>  ' . __('Delete'), array('action' => 'delete', $this->Form->value('User.id')), array('escape'=>false, 'class'=>'btn btn-danger'), __('Are you sure you want to delete # %s?', $this->Form->value('User.id'))); ?></li>
		<li><?php echo $this->Html->link('<i class="icon-th-list"></i>  ' . __d('amanager', 'List Users'), array('controller' => 'users', 'action' => 'index'), array('escape'=>false, 'class'=>'btn')); ?> </li>
		<li><?php echo $this->Html->link('<i class="icon-plus-sign"></i>  ' . __d('amanager', 'List Groups'), array('controller' => 'groups', 'action' => 'index'), array('class'=>'btn', 'escape'=>false)); ?> </li>
		<li><?php echo $this->Html->link('<i class="icon-th-list"></i>  ' . __d('amanager', 'New Group'), array('controller' => 'groups', 'action' => 'add'), array('class'=>'btn', 'escape'=>false)); ?> </li>
	</ul>
</div>
