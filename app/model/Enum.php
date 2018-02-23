<?php /*
<fusedoc>
	<history version="1.0">
		- first commit
	</history>
	<history version="1.1">
		- fix bug : order by seq then by key
		- apply LIKE when comparing [type] and [key] in order to allow getting records by wildcard (%)
	</history>
</fusedoc>
*/
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
		if ( empty($key) or stripos($key, '%') !== false ) {
			return R::find('enum', $filter.$order, $filterParam);
		// or single value
		} else {
			return R::findOne('enum', $filter.$order, $filterParam);
		}
	}


	// get disabled items as well
	public static function getAll($type, $key=null) {
		return self::get($type, $key, true);
	}


	// get multiple enum records as array
	public static function getArray($type) {
		$beans = self::get($type);
		return self::toArray($beans);
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


}