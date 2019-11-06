<?php
// breadcrumb
$arguments['breadcrumb'] = array('Settings', ucwords( str_replace('_', ' ', strtolower($_SESSION['enumController__enumType']) ) ) );


// tab layout config
$tabLayout = array(
	'style' => 'tabs',
	'position' => 'left',
	'header' => '<h3>Settings</h3>',
	'nav' => call_user_func(function(){
		$menus = array();
		// existing types
		$types = R::getCol('SELECT DISTINCT type FROM enum ORDER BY type');
		foreach ( $types as $item ) {
			$menus[] = array(
				'name' => ucwords( str_replace('_', ' ', strtolower($item) ) ),
				'url' => F::url( F::command('controller').'&type='.$item ),
				'active' => ( !empty($_SESSION['enumController__enumType']) and $_SESSION['enumController__enumType'] == $item ),
				'remark' => R::count('enum', 'type = ? AND disabled = 0', array($item)),
			);
		}
		// new type
		$menus[] = array(
			'name' => '+ New Setting',
			'url' => F::url( F::command('controller').'&type' ),
			'active' => empty($_SESSION['enumController__enumType']),
			'linkClass' => 'font-italic text-muted',
		);
		// done!
		return $menus;
	}),
);


// tab layout
ob_start();
include F::config('appPath').'view/global/tab.php';
$layout['content'] = ob_get_clean();


// global layout
$layout['width'] = 'full';
include F::config('appPath').'view/global/layout.php';