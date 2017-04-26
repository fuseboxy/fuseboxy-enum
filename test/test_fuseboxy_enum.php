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
		if ( !class_exists('Auth') ) {
			include dirname(__DIR__).'/app/model/Enum.php';
		}
		if ( !class_exists('R') ) {
			include __DIR__.'/utility-enum/redbeanphp/4.3.3/rb.php';
			include __DIR__.'/utility-enum/config/rb_config.php';
		}
	}


	function test__Enum__get() {
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
		$bean = Enum::get('UNIT_TEST', 'this-is-b');
		$this->assertTrue( !empty($bean->id) );
		$this->assertTrue( $bean->value == 'This is B' );
		$this->assertTrue( $bean->remark == 'BB' );
		// valid type & key (excluding disabled)
		$bean = Enum::get('UNIT_TEST', 'this-is-c');
		$this->assertTrue( empty($bean->id) );
		// valid type & key (including disabled)
		$bean = Enum::get('UNIT_TEST', 'this-is-c', true);
		$this->assertTrue( !empty($bean->id) );
		$this->assertTrue( $bean->value == 'This is C' );
		$this->assertTrue( $bean->remark == 'CCC' );
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


	function test__Enum__getArray() {
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
		$arr = Enum::getArray('FOO_BAR');
		$this->assertTrue( count($arr) == 0 );
		// valid type (active only)
		$arr = Enum::getArray('UNIT_TEST');
		$this->assertTrue ( count($arr) == 3 );
		$this->assertTrue ( isset($arr['this-is-a']) and $arr['this-is-a'] == 'This is A' );
		$this->assertFalse( isset($arr['this-is-c']) );
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


} // TestFuseboxyEnum