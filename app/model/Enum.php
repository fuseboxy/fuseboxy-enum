<?php
class Enum {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }
	// cache for current request
	private static $cache = array();
	public static function cache($type) { return self::$cache[$type] ?? null; }




	// alias methods (for backward compatibility)
	public static function getAll   ($type)                                    { return self::all($type); }
	public static function getArray ($type, $key=null, $includeDisabled=false) { return self::array($type, $key, $includeDisabled); }
	public static function getFirst ($type, $includeDisabled=false)            { return self::first($type, $includeDisabled); }
	public static function getRemark($type, $key, $var=false)                  { return self::remark($type, $key, $var); }
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
				<structure name="$cache" scope="self">
					<structure name="~type~">
						<object name="~id~" />
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$type" />
			</in>
			<out>
				<!-- cache -->
				<structure name="$cache" scope="self">
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
		// load from database (when necessary)
		if ( !self::cache($type) ) {
			$data = ORM::get('enum', ' `type` LIKE ? ORDER BY IFNULL(`seq`, 99999), `key` ASC ', [ $type ]);
			// validation
			if ( $data === false ) {
				self::$error = ORM::error();
				return false;
			}
			// put into cache
			foreach ( $data as $id => $item ) {
				if ( !self::cache($item->type) ) self::$cache[$item->type] = array();
				self::$cache[$item->type[$id] = $item;
			}
		}
		// if still not found
		// ===> type not found in database
		// ===> return empty array
		if ( !self::cache($type) ) return array();
		// done!
		return self::cache($type);
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
		// when record has ID
		// ===> user passed key-without-wildcard
		// ===> therefore specific enum obtained
		// ===> turn into array instead
		if ( !empty($beans->id) ) {
			$key = $beans->key;
			// convert language (when necessary)
			if ( class_exists('I18N') ) {
				$val = I18N::convert($beans, 'value');
				if ( $val === false ) {
					self::$error = I18N::error();
					return false;
				}
			} else $val = $beans->value;
			return array($key => $val);
		}
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
				<structure name="$cache" scope="self" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function clearCache() {
		self::$cache = array();
		return true;
	}




	/**
	<fusedoc>
		<description>
			get number of items by type
		</description>
		<io>
			<in>
				<string name="$type" />
				<boolean name="$includeDisabled" optional="yes" default="false" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function count($type, $includeDisabled=false) {
		$items = self::get($type, null, $includeDisabled);
		if ( $items === false ) return false;
		return count($items);
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
			$isPassedWildcardCheck = ( !self::hasWildcard($key) or preg_match('/'.str_replace('%', '*', $key).'/', $item->key) );
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
			get remark of specific enum item (or specific var in remark)
			===> simply return empty when not found
		</description>
		<io>
			<in>
				<string name="$type" />
				<string name="$key" />
				<mixed name="$var" default="false" comments="return parsed remark when true; return specific var of parsed remark when string" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function remark($type, $key, $var=false) {
		// get specific item (if any)
		$item = self::get($type, $key);
		if ( $item === false ) return false;
		// convert language (when necessary)
		$result = class_exists('I18N') ? I18N::convert($item, 'remark') : $item->remark;
		if ( $result === false and class_exists('I18N') ) {
			self::$error = I18N::error();
			return false;
		}
		// parse remark & get specific var (when necessary)
		if ( !empty($var) ) parse_str($result, $parsed);
		if ( $var === true ) return $parsed;
		if ( !empty($var) and is_string($var) ) return $parsed[$var] ?? null;
		// done!
		return $result;
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
		// go through each item
		foreach ( $beans as $item ) {
			// convert language (when necessary)
			if ( class_exists('I18N') ) {
				$result[$item->key] = I18N::convert($item, 'value');
				if ( $result[$item->key] === false ) {
					self::$error = I18N::error();
					return false;
				}
			} else $result[$item->key] = $item->value;
		}
		// done!
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
		// get specific item (if any)
		$item = self::get($type, $key);
		if ( $item === false ) return false;
		if ( empty($item->id) and $returnKeyIfNotFound ) return $key;
		// convert language (when necessary)
		if ( class_exists('I18N') ) {
			$result = I18N::convert($item, 'value');
			if ( $result === false ) {
				self::$error = I18N::error();
				return false;
			}
		} else $result = $item->value;
		// done!
		return $result;
	}


} // class