<?php
// tab layout config
$tabLayout = array(
	'style' => 'tab',
	'position' => 'left',
	'header' => '<h3>Settings</h3>',
	'nav' => array(),
);


// tab : all existing types
$types = R::getCol('SELECT DISTINCT type FROM enum ORDER BY type');
foreach ( $types as $item ) {
	$tabLayout['nav'][] = array(
		'name' => ucwords( str_replace('_', ' ', strtolower($item) ) ),
		'url' => F::url("{$fusebox->controller}&type={$item}"),
		'active' => ( !empty($_SESSION['enumController__enumType']) and $_SESSION['enumController__enumType'] == $item ),
		'remark' => R::count('enum', 'type = ? AND disabled = 0', array($item)),
	);
}


// tab : new type
$tabLayout['nav'][] = array(
	'name' => '<em class="text-muted">+ New Setting</em>',
	'url' => F::url("{$fusebox->controller}&type=(new)"),
	'active' => ( empty($_SESSION['enumController__enumType']) or $_SESSION['enumController__enumType'] == '(new)' )
);


// tab layout
ob_start();
include F::config('appPath').'view/global/tab.php';
$layout['content'] = ob_get_clean();


// wrap by global layout
include F::config('appPath').'view/global/layout.php';