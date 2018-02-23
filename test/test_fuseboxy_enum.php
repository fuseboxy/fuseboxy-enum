<?php
class TestFuseboxyEnum extends UnitTestCase {


	function __construct() {
		if ( !class_exists('Framework') ) {
			include __DIR__.'/utility-enum/framework/1.0.3/fuseboxy.php';
			Framework::$mode = Framework::FUSEBOX_UNIT_TEST;
			Framework::$configPath = __DIR__.'/utility-enum/config/fusebox_config.php';
		}
		if ( !class_exists('F') ) {
			include __DIR__.'/utility-enum/framework/1.0.3/F.php';
		}
		if ( !class_exists('Enum') ) {
			include dirname(__DIR__).'/app/model/Enum.php';
		}
		if ( !class_exists('Auth') ) {
			include __DIR__.'/utility-enum/model/Auth.php';
		}
		if ( !class_exists('Sim') ) {
			include __DIR__.'/utility-enum/model/Sim.php';
		}
		if ( !class_exists('R') ) {
			include __DIR__.'/utility-enum/redbeanphp/4.3.3/rb.php';
			include __DIR__.'/utility-enum/config/rb_config.php';
		}
	}


	function test__Enum__get() {
		// create dummy records
		$keys = array('this-is-a', 'she-is-b', 'this-is-c', 'he-is-d');
		$values = array('This is A', 'She is B', 'This is C', 'He is D');
		$remarks = array('A', 'BB', 'CCC', 'DDDD');
		foreach ( $keys as $i => $key ) {
			$bean = R::dispense('enum');
			$bean->import(array(
				'type'     => 'UNIT_TEST',
				'key'      => $key,
				'value'    => $values[$i],
				'remark'   => $remarks[$i],
				'seq'      => $i,
				'disabled' => ( $key == 'this-is-c' ),
			));
			$id = R::store($bean);
			$this->assertTrue( !empty($id) );
		}
		// invalid type
		$beans = Enum::get('FOO_BAR');
		$this->assertTrue( count($beans) == 0 );
		// invalid type & key
		$bean = Enum::get('FOO_BAR', 'foobar');
		$this->assertTrue( empty($bean->id) );
		// invalid type & valid key
		$bean = Enum::get('FOO_BAR', 'this-is-a');
		$this->assertTrue( empty($bean->id) );
		// valid type & invalid key
		$bean = Enum::get('UNIT_TEST', 'foobar');
		$this->assertTrue( empty($bean->id) );
		// valid type (active only)
		$beans = Enum::get('UNIT_TEST');
		$this->assertTrue( count($beans) == 3 );
		// valid type (including disabled)
		$beans = Enum::get('UNIT_TEST', null, true);
		$this->assertTrue( count($beans) == 4 );
		// valid type & key (active only)
		$bean = Enum::get('UNIT_TEST', 'she-is-b');
		$this->assertTrue( !empty($bean->id) );
		$this->assertTrue( $bean->value == 'She is B' );
		$this->assertTrue( $bean->remark == 'BB' );
		// valid type & key (excluding disabled)
		$bean = Enum::get('UNIT_TEST', 'this-is-c');
		$this->assertTrue( empty($bean->id) );
		// valid type & key (including disabled)
		$bean = Enum::get('UNIT_TEST', 'this-is-c', true);
		$this->assertTrue( !empty($bean->id) );
		$this->assertTrue( $bean->value == 'This is C' );
		$this->assertTrue( $bean->remark == 'CCC' );
		// check get by wildcard
		$beans = Enum::get('UNIT_TEST', 'this-is-%');
		$this->assertTrue( count($beans) == 1 );
		$beans = Enum::get('UNIT_TEST', 'this-is-%', true);
		$this->assertTrue( count($beans) == 2 );
		$beans = Enum::get('U%T');
		$this->assertTrue( count($beans) == 3 );
		// check sequence (order by seq when specified)
		$beans = Enum::get('UNIT_TEST', null, true);
		$firstBean = array_shift($beans);
		$lastBean = array_pop($beans);
		$this->assertTrue( $firstBean->key == 'this-is-a' );
		$this->assertTrue( $lastBean->key == 'he-is-d' );
		// check sequence (order by seq then by key)
		R::exec('UPDATE `enum` SET `seq` = 0');
		$beans = Enum::get('UNIT_TEST', null, true);
		$firstBean = array_shift($beans);
		$lastBean = array_pop($beans);
		$this->assertTrue( $firstBean->key == 'he-is-d' );
		$this->assertTrue( $lastBean->key == 'this-is-c' );
		// clean-up
		R::nuke();
	}


	function test__Enum__getAll() {
		// create dummy records
		$keys = array('this-is-a', 'this-is-b', 'this-is-c', 'this-is-d');
		$values = array('This is A', 'This is B', 'This is C', 'This is D');
		$remarks = array('A', 'BB', 'CCC', 'DDDD');
		foreach ( $keys as $i => $key ) {
			$bean = R::dispense('enum');
			$bean->import(array(
				'type'     => 'UNIT_TEST',
				'key'      => $key,
				'value'    => $values[$i],
				'remark'   => $remarks[$i],
				'seq'      => $i,
				'disabled' => ( $key == 'this-is-c' ),
			));
			$id = R::store($bean);
			$this->assertTrue( !empty($id) );
		}
		// invalid type
		$beans = Enum::getAll('FOO_BAR');
		$this->assertTrue( count($beans) == 0 );
		// valid type
		$beans = Enum::getAll('UNIT_TEST');
		$this->assertTrue( count($beans) == 4 );
		// valid type & key
		$bean = Enum::getAll('UNIT_TEST', 'this-is-c');
		$this->assertTrue( !empty($bean->id) );
		$this->assertTrue( $bean->value == 'This is C' );
		$this->assertTrue( $bean->remark == 'CCC' );
		// clean-up
		R::nuke();
	}


	function test__Enum__getFirst() {
		// create dummy records
		$keys = array('this-is-a', 'this-is-b', 'this-is-c', 'this-is-d');
		$values = array('This is A', 'This is B', 'This is C', 'This is D');
		$remarks = array('A', 'BB', 'CCC', 'DDDD');
		foreach ( $keys as $i => $key ) {
			$bean = R::dispense('enum');
			$bean->import(array(
				'type'     => 'UNIT_TEST',
				'key'      => $key,
				'value'    => $values[$i],
				'remark'   => $remarks[$i],
				'seq'      => $i,
				'disabled' => ( $key == 'this-is-a' ),
			));
			$id = R::store($bean);
			$this->assertTrue( !empty($id) );
		}
		// invalid type
		$bean = Enum::getFirst('FOO_BAR');
		$this->assertTrue( empty($bean->id) );
		// valid type
		$bean = Enum::getFirst('UNIT_TEST');
		$this->assertTrue( $bean->key == 'this-is-b' );
		// valid type (include disabled items)
		$bean = Enum::getFirst('UNIT_TEST', true);
		$this->assertTrue( $bean->key == 'this-is-a' );
		// clean-up
		R::nuke();
	}


	function test__Enum__getArray() {
		// create dummy records
		$keys = array('this-is-a', 'this-is-b', 'this-is-c', 'i-am-d');
		$values = array('This is A', 'This is B', 'This is C', 'I am D');
		$remarks = array('A', 'BB', 'CCC', 'DDDD');
		foreach ( $keys as $i => $key ) {
			$bean = R::dispense('enum');
			$bean->import(array(
				'type'     => 'UNIT_TEST',
				'key'      => $key,
				'value'    => $values[$i],
				'remark'   => $remarks[$i],
				'seq'      => $i,
				'disabled' => ( $key == 'this-is-c' ),
			));
			$id = R::store($bean);
			$this->assertTrue( !empty($id) );
		}
		// invalid type
		$arr = Enum::getArray('FOO_BAR');
		$this->assertTrue( count($arr) == 0 );
		// valid type (active only)
		$arr = Enum::getArray('UNIT_TEST');
		$this->assertTrue ( count($arr) == 3 );
		$this->assertTrue ( isset($arr['this-is-a']) and $arr['this-is-a'] == 'This is A' );
		$this->assertFalse( isset($arr['this-is-c']) );
		// valid type (including disabled)
		$arr = Enum::getArray('UNIT_TEST', null, true);
		// get by wildcard (type only)
		$arr = Enum::getArray('U%T');
		$this->assertTrue ( count($arr) == 3 );
		$this->assertTrue ( isset($arr['this-is-a']) and $arr['this-is-a'] == 'This is A' );
		$this->assertFalse( isset($arr['this-is-c']) );
		// get by wildcard (type & key)
		$arr = Enum::getArray('UNIT_TEST', 'this-is-%');
		$this->assertTrue( count($arr) == 2 );
		$arr = Enum::getArray('UNIT_TEST', 'this-is-%', true);
		$this->assertTrue( count($arr) == 3 );
		$arr = Enum::getArray('UNIT_TEST', 'this-is-a');
		$this->assertTrue( count($arr) == 1 );
		$arr = Enum::getArray('UNIT_TEST', 'this-is-c');
		$this->assertTrue( count($arr) == 0 );
		$arr = Enum::getArray('UNIT_TEST', 'this-is-c', true);
		$this->assertTrue( count($arr) == 1 );
		// clean-up
		R::nuke();
	}


	function test__Enum__getValue() {
		// create dummy records
		$keys = array('this-is-a', 'this-is-b', 'this-is-c', 'this-is-d');
		$values = array('This is A', 'This is B', 'This is C', 'This is D');
		$remarks = array('A', 'BB', 'CCC', 'DDDD');
		foreach ( $keys as $i => $key ) {
			$bean = R::dispense('enum');
			$bean->import(array(
				'type'     => 'UNIT_TEST',
				'key'      => $key,
				'value'    => $values[$i],
				'remark'   => $remarks[$i],
				'seq'      => $i,
				'disabled' => ( $key == 'this-is-c' ),
			));
			$id = R::store($bean);
			$this->assertTrue( !empty($id) );
		}
		// invalid item (default return key-param)
		$result = Enum::getValue('UNIT_TEST', 'foobar');
		$this->assertTrue( $result == 'foobar' );
		// invalid item (choose to return empty when not found)
		$result = Enum::getValue('UNIT_TEST', 'foobar', false);
		$this->assertTrue( $result == '' );
		// valid item (active only)
		$result = Enum::getValue('UNIT_TEST', 'this-is-d');
		$this->assertTrue( $result == 'This is D' );
		// valid item (excluding disabled)
		$result = Enum::getValue('UNIT_TEST', 'this-is-c');
		$this->assertTrue( $result == 'this-is-c' );
		$result = Enum::getValue('UNIT_TEST', 'this-is-c', false);
		// clean-up
		R::nuke();
	}


	function test__Enum__toArray() {
		// create dummy records
		$keys = array('this-is-a', 'this-is-b', 'this-is-c', 'this-is-d');
		$values = array('This is A', 'This is B', 'This is C', 'This is D');
		$remarks = array('A', 'BB', 'CCC', 'DDDD');
		foreach ( $keys as $i => $key ) {
			$bean = R::dispense('enum');
			$bean->import(array(
				'type'     => 'UNIT_TEST',
				'key'      => $key,
				'value'    => $values[$i],
				'remark'   => $remarks[$i],
				'seq'      => $i,
				'disabled' => ( $key == 'this-is-c' ),
			));
			$id = R::store($bean);
			$this->assertTrue( !empty($id) );
		}
		// invalid type (nothing happens)
		$beans = Enum::get('FOO_BAR');
		$arr = Enum::toArray($beans);
		$this->assertTrue( $arr == array() );
		// valid type (convert into key-value pairs)
		$beans = Enum::get('UNIT_TEST');
		$arr = Enum::toArray($beans);
		$this->assertTrue ( count($arr) == 3 );
		$this->assertTrue ( isset($arr['this-is-a']) );
		$this->assertFalse( isset($arr['this-is-c']) );
		$this->assertTrue ( $arr['this-is-d'] == 'This is D' );
		// clean-up
		R::nuke();
	}


	function test__enumController__index() {
		global $fusebox;
		Framework::createAPIObject();
		Framework::loadConfig();
		Framework::setMyself();
		$fusebox->action = 'index';
		// create dummy user
		$bean = R::dispense('user');
		$bean->import(array(
			'username' => 'foobar',
			'role'     => 'USER',
		));
		$id = R::store($bean);
		$this->assertTrue( !empty($id) );
		// only accessible after login
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/enum_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
			$this->assertPattern('/fuseaction=auth/i', $e->getMessage());
		}
		$this->assertTrue($hasRedirect);
		// only accessible by super or admin
		$loginResult = Auth::login('foobar', Auth::SKIP_PASSWORD_CHECK);
		$this->assertTrue( $loginResult );
		try {
			$hasRedirect = false;
			ob_start();
			include dirname(__DIR__).'/app/controller/enum_controller.php';
			$output = ob_get_clean();
		} catch (Exception $e) {
			$output = $e->getMessage();
			$hasRedirect = ( $e->getCode() == Framework::FUSEBOX_REDIRECT );
			$this->assertPattern("/fuseaction={$fusebox->config['defaultCommand']}/i", $e->getMessage());
		}
		$this->assertTrue($hasRedirect);
		// clean-up
		$fusebox = null;
		Auth::logout();
		R::nuke();
	}


} // TestFuseboxyEnum