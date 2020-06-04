<?php
F::redirect('auth', !Auth::user());
F::redirect(F::config('defaultCommand'), !Auth::activeUserInRole('SUPER,ADMIN'));


// default type
if ( !isset($_SESSION['enumController__enumType']) ) {
	$_SESSION['enumController__enumType'] = ORM::first('enum', 'ORDER BY type')->type;
}


// change selected type
if ( isset($arguments['type']) ) {
	$_SESSION['enumController__enumType'] = $arguments['type'];
}


// config
$scaffold = array(
	'beanType' => 'enum',
	'editMode' => 'inline',
	'allowDelete' => Auth::activeUserInRole('SUPER'),
	'layoutPath' => F::appPath('view/enum/layout.php'),
	'listFilter' => array('type = ?', array($_SESSION['enumController__enumType'])),
	'listOrder' => 'ORDER BY IFNULL(seq, 9999), `key` ',
	'listField' => array(
		'id' => '5%',
		'key|type' => '15%',
		'value|remark' => '50%',
		'seq' => '7%'
	),
	'fieldConfig' => array(
		'id' => array(),
		'type' => array('placeholder' => true, 'readonly' => !Auth::activeUserInRole('SUPER'), 'default' => $_SESSION['enumController__enumType']),
		'key' => array('placeholder' => true),
		'value' => array('placeholder' => true),
		'remark' => array('placeholder' => true),
		'seq' => array('placeholder' => true),
	),
	'writeLog' => class_exists('Log'),
);


// component
include F::appPath('controller/scaffold_controller.php');