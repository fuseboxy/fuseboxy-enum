<?php
class Enum {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


	// get multiple enum beans by type
	// get single enum bean by type & key
	public static function get($type, $key=null, $all=false) {
		// filter
		$filter = '`type` LIKE ? ';
		if ( empty($all) ) $filter .= 'AND IFNULL(disabled, 0) = 0 ';
		$filterParam = array($type);
		if ( !empty($key) ) {
			$filter .= "AND `key` LIKE ? ";
			$filterParam[] = $key;
		}
		// order
		$order = 'ORDER BY IFNULL(`seq`, 9999), `key` ASC ';
		// get multi records
		if ( empty($key) or self::__hasWildcard($key) ) {
			return R::find('enum', $filter.$order, $filterParam);
		// or single value
		} else {
			return R::findOne('enum', $filter.$order, $filterParam);
		}
	}


	// get first item of specific type
	public static function getFirst($type, $all=false) {
		$beans = self::get($type, null, $all);
		return !empty($beans) ? array_shift($beans) : R::dispense('enum');
	}


	// get disabled items as well
	public static function getAll($type, $key=null) {
		return self::get($type, $key, true);
	}


	// get multiple enum records as array
	public static function getArray($type, $key=null, $all=false) {
		$beans = self::get($type, $key, $all);
		// check if multiple or single
		if ( empty($key) or self::__hasWildcard($key) ) {
			return self::toArray($beans);
		} elseif ( !empty($beans->id) ) {
			return array( $beans->key => $beans->value );
		} else {
			return array();
		}
	}


	// get specific enum value
	public static function getValue($type, $key, $returnKeyIfNotFound=true) {
		$result = self::get($type, $key);
		if ( empty($result->id) ) {
			return $returnKeyIfNotFound ? $key : '';
		} else {
			return $result->value;
		}
	}


	// transform multiple enum records to array
	public static function toArray($beans) {
		$result = array();
		foreach ( $beans as $b ) $result[$b->key] = $b->value;
		return $result;
	}


	// check is there any sql wildcard
	private static function __hasWildcard($str) {
		return ( stripos($str, '%') !== false );
	}


} // class