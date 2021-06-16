<?php
class Enum {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	// alias methods (for backward compatibility)
	public static function getAll   ($type)                                    { return self::all($type); }
	public static function getArray ($type, $key=null, $includeDisabled=false) { return self::array($type, $key, $includeDisabled); }
	public static function getFirst ($type, $includeDisabled=false)            { return self::first($type, $includeDisabled); }
	public static function getRemark($type, $key)                              { return self::remark($type, $key); }
	public static function getValue ($type, $key, $returnKeyIfNotFound=true)   { return self::value($type, $key, $returnKeyIfNotFound); }




	/**
	<fusedoc>
		<description>
			get all items (included disabled) of specific type
			load from cache when available
		</description>
		<io>
			<in>
				<!-- cache -->
				<structure name="__enum__" scope="$GLOBALS" optional="yes">
					<structure name="~type~">
						<object name="~id~" />
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$type" />
			</in>
			<out>
				<!-- cache -->
				<structure name="__enum__" scope="$GLOBALS">
					<structure name="~type~">
						<object name="~id~" />
					</structure>
				</structure>
				<!-- return value -->
				<structure name="~return~">
					<number name="id" />
					<string name="type" />
					<string name="key" />
					<string name="value" />
					<string name="remark" />
					<boolean name="disabled" />
				</object>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($type) {
		// create cache container (when necessary)
		if ( !isset($GLOBALS['__enum__']) ) $GLOBALS['__enum__'] = array();
		// load from database (when necessary)
		if ( !isset($GLOBALS['__enum__'][$type]) ) {
			$data = ORM::get('enum', ' `type` LIKE ? ORDER BY IFNULL(`seq`, 99999), `key` ASC ', [ $type ]);
			// validation
			if ( $data === false ) {
				self::$error = ORM::error();
				return false;
			}
			// put into cache
			foreach ( $data as $id => $item ) {
				if ( !isset($GLOBALS['__enum__'][$item->type]) ) $GLOBALS['__enum__'][$item->type] = array();
				$GLOBALS['__enum__'][$item->type][$id] = $item;
			}
		}
		// if still not found
		// ===> type not found in database
		// ===> return empty array
		if ( !isset($GLOBALS['__enum__'][$type]) ) return array();
		// done!
		return $GLOBALS['__enum__'][$type];
	}




	/**
	<fusedoc>
		<description>
			get multiple items as an array
		</description>
		<io>
			<in>
				<string name="$type" />
				<string name="$keyWithWildcard" optional="yes" comments="supposed to have wildcard" />
				<boolean name="$includeDisabled" optional="yes" default="false" />
			</in>
			<out>
				<structure name="~return~">
					<string name="~enumKey~" value="~enumValue~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function array($type, $keyWithWildcard=null, $includeDisabled=false) {
		// load related items
		$beans = self::get($type, $keyWithWildcard, $includeDisabled);
		if ( $beans === false ) return false;
		// when user passed key-without-wildcard
		// ===> single record obtained
		// ===> turn into array instead
		if ( !empty($beans->id) ) return array($beans->key => $beans->value);
		// done!
		return self::toArray($beans);
	}




	/**
	<fusedoc>
		<description>
			get first item of specific type
		</description>
		<io>
			<in>
				<string name="$type" />
				<boolean name="$includeDisabled" optional="yes" default="false" />
			</in>
			<out>
				<object name="~return~">
					<string name="type" />
					<string name="key" />
					<string name="value" />
					<string name="remark" />
				</object>
			</out>
		</io>
	</fusedoc>
	*/
	public static function first($type, $includeDisabled=false) {
		// load all of this type (from cache)
		$all = self::all($type);
		if ( $all === false ) return false;
		// find first match
		// ===> return right away
		foreach ( $all as $id => $item ) if ( !$item->disabled or $includeDisabled ) return $item;
		// when no match
		// ===> empty bean (when not found)
		$empty = ORM::new('enum');
		if ( $empty === false ) {
			self::$error = ORM::error();
			return false;
		}
		// done!
		return $empty;
	}




	/**
	<fusedoc>
		<description>
			clear cached data (for unit test)
		</description>
		<io>
			<in>
				<structure name="__enum__" scope="$GLOBALS" optional="yes" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function clearCache() {
		if ( isset($GLOBALS['__enum__']) ) unset($GLOBALS['__enum__']);
		return true;
	}




	/**
	<fusedoc>
		<description>
			get single item by type & key
			get multiple items by type
			get multiple items by type & key-with-wildcard
		</description>
		<io>
			<in>
				<string name="$type" />
				<string name="$key" optional="yes" example="home-applicance|home-%|*-applicance" />
				<boolean name="$includeDisabled" optional="yes" default="false" />
			</in>
			<out>
				<!-- multiple -->
				<structure name="~return~" optional="yes">
					<object name="~id~">
						<string name="type" example="PRODUCT_CATEGORY" />
						<string name="key" example="home-appliance" />
						<string name="value" example="Home Appliances" />
						<string name="remark" />
					</object>
				</structure>
				<!-- single -->
				<object name="~return~">
					<string name="type" />
					<string name="key" />
					<string name="value" />
					<string name="remark" />
				</object>
			</out>
		</io>
	*/
	public static function get($type, $key=null, $includeDisabled=false) {
		// load all of this type (from cache)
		$all = self::all($type);
		if ( $all === false ) return false;
		// get single item (when necessary)
		// ===> find first match & return right away
		// ===> otherwise, return empty bean
		if ( !empty($key) and !self::hasWildcard($key) ) {
			foreach ( $all as $id => $item ) if ( $item->key == $key and ( !$item->disabled or $includeDisabled ) ) return $item;
			$empty = ORM::new('enum');
			if ( $empty === false ) {
				self::$error = ORM::error();
				return false;
			}
			return $empty;
		}
		// get multiple items
		// ===> filter by disabled field (when necessary)
		// ===> filter by key-with-wildcard (when necessary)
		$result = array();
		foreach ( $all as $id => $item ) {
			$isPassedDisabledCheck = ( !$item->disabled or $includeDisabled );
			$isPassedWildcardCheck = ( !self::hasWildcard($key) or preg_match('/'.str_replace('%', '*', $key), $item->key) );
			if ( $isPassedDisabledCheck and $isPassedWildcardCheck ) $result[$id] = $item;
		}
		return $result;
	}




	/**
	<fusedoc>
		<description>
			check whether the string has SQL wildcard character
		</description>
		<io>
			<in>
				<string name="$str" example="home-%|home-*" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	private static function hasWildcard($str) {
		return ( strpos($str, '%') !== false ) or ( strpos($str, '*') !== false );
	}




	/**
	<fusedoc>
		<description>
			get remark of specific enum item
			===> simply return empty when not found
		</description>
		<io>
			<in>
				<string name="$type" />
				<string name="$key" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function remark($type, $key) {
		$item = self::get($type, $key);
		if ( $item === false ) return false;
		return $item->remark;
	}




	/**
	<fusedoc>
		<description>
			convert multiple enum beans to array
		</description>
		<io>
			<in>
				<structure name="$beans">
					<string name="type" />
					<string name="key" />
					<string name="value" />
					<string name="remark" />
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<string name="~key~" value="~value~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function toArray($beans) {
		$result = array();
		foreach ( $beans as $item ) $result[$item->key] = $item->value;
		return $result;
	}




	/**
	<fusedoc>
		<description>
			get value of specific enum item
		</description>
		<io>
			<in>
				<string name="$type" />
				<string name="$key" />
				<boolean name="$returnKeyIfNotFound" optional="yes" default="true" comments="return empty string otherwise" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function value($type, $key, $returnKeyIfNotFound=true) {
		$item = self::get($type, $key);
		if ( $item === false ) return false;
		if ( empty($item->id) and $returnKeyIfNotFound ) return $key;
		return $item->value;
	}


} // class