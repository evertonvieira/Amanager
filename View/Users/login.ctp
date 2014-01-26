<?php
echo $this->Session->flash('auth');
echo $this->Form->create('User', array('url' => array('controller' =>'users', 'action' => 'login'), 'class'=>'form-signin'));
?>
<h2><?php echo __('Please sign in', true); ?></h2>
<?php
echo $this->Form->input('User.username', array('label'=>'E-mail', 'class'=>'input-block-level', 'placeholder'=>__('Email address')));
echo $this->Form->input('User.password', array('label'=>'Senha', 'class'=>'input-block-level', 'placeholder'=>__('Password')));
echo $this->Form->submit(__('Login'), array('class'=>'btn btn-large btn-primary'));
echo $this->Html->link(__('Forgot password'), array('plugin'=>'amanager', 'controller'=>'users', 'action'=>'forgot_password' ));
echo $this->Form->end();
?>