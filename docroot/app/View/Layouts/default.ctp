<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
 	<title><?php echo $title_for_layout; ?></title>
	<?php
	echo $this->Html->meta('icon');
	echo $this->Html->css('bootstrap.min.css');
	echo $this->Html->css('datepicker.css');
	echo $this->Html->css('jquery.jOrgChart.css');
	echo $this->Html->css('main');

	echo $this->fetch('meta');
	?>
</head>
<body>
	<?php echo $this->Session->flash(); ?>

	<?php echo $this->fetch('content'); ?>

	<?php echo $this->element('sql_dump'); ?>

	<!-- jQuery includes -->
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
	<!-- end jQuery includes -->

	<?php echo $this->Html->script('bootstrap.min.js', array('inline' => true)); ?>
	<?php echo $this->Html->script('bootstrap-datepicker.js', array('inline' => true)); ?>
	<?php echo $this->Html->script('jquery.jOrgChart.js', array('inline' => true)); ?>
	<?php echo $this->Html->script('main.js', array('inline' => true)); ?>
</body>
</html>
