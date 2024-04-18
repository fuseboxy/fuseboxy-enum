<?php
class Enum {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }
	// cache for current request
	private static $cache = array();
	public static function cache($enumType) { return self::$cache[$enumType] ?? null; }




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
				<string name="$enumType" />
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
	public static function all($enumType) {
		// load from database (when necessary)
		if ( !self::cache($enumType) ) {
			$data = ORM::get('enum', ' `type` LIKE ? ORDER BY IFNULL(`seq`, 99999), `key` ASC ', [ $enumType ]);
			// validation
			if ( $data === false ) {
				self::$error = '[Enum::all] '.ORM::error();
				return false;
			}
			// put into cache
			foreach ( $data as $id => $item ) {
				if ( !self::cache($item->type) ) self::$cache[$item->type] = array();
				self::$cache[$item->type][$id] = $item;
			}
		}
		// if still not found
		// ===> type not found in database
		// ===> return empty array
		if ( !self::cache($enumType) ) return array();
		// done!
		return self::cache($enumType);
	}




	/**
	<fusedoc>
		<description>
			get multiple items as an array
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<string name="$enumKeyWithWildcard" optional="yes" comments="supposed to have wildcard" />
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
	public static function array($enumType, $enumKeyWithWildcard=null, $includeDisabled=false) {
		// load related items
		$beans = self::get($enumType, $enumKeyWithWildcard, $includeDisabled);
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
					self::$error = '[Enum::array] '.I18N::error();
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
			get multiple items as an array (with key as value)
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<string name="$enumKeyWithWildcard" optional="yes" comments="supposed to have wildcard" />
				<boolean name="$includeDisabled" optional="yes" default="false" />
			</in>
			<out>
				<structure name="~return~">
					<string name="~enumKey~" value="~enumKey~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function arrayWithKeyAsValue($enumType, $enumKeyWithWildcard=null, $includeDisabled=false) {
		$result = self::array($enumType, $enumKeyWithWildcard, $includeDisabled);
		if ( $result === false ) return false;
		foreach ( $result as $key => $val ) $result[$key] = $key;
		return $result;
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
				<string name="$enumType" />
				<boolean name="$includeDisabled" optional="yes" default="false" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function count($enumType, $includeDisabled=false) {
		$items = self::get($enumType, null, $includeDisabled);
		if ( $items === false ) return false;
		return count($items);
	}




	/**
	<fusedoc>
		<description>
			check if specific enum exists
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<string name="$enumKey" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function exists($enumType, $enumKey) {
		$item = self::get($enumType, $enumKey);
		return !empty($item->id);
	}




	/**
	<fusedoc>
		<description>
			get first item of specific type
		</description>
		<io>
			<in>
				<string name="$enumType" />
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
	public static function first($enumType, $includeDisabled=false) {
		// load all of this type (from cache)
		$all = self::all($enumType);
		if ( $all === false ) return false;
		// find first match
		// ===> return right away
		foreach ( $all as $id => $item ) if ( !$item->disabled or $includeDisabled ) return $item;
		// when no match
		// ===> empty bean (when not found)
		$empty = ORM::new('enum');
		if ( $empty === false ) {
			self::$error = '[Enum::first] '.ORM::error();
			return false;
		}
		// done!
		return $empty;
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
				<string name="$enumType" />
				<string name="$enumKey" optional="yes" example="home-applicance|home-%|*-applicance" />
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
	</fusedoc>
	*/
	public static function get($enumType, $enumKey=null, $includeDisabled=false) {
		$result = array();
		// load all of this type (from cache)
		$all = self::all($enumType);
		if ( $all === false ) return false;
		// when key specified & no wildcard
		// ===> get single item
		if ( !empty($enumKey) and !self::hasWildcard($enumKey) ) {
			// return first match (when found in cache)
			foreach ( $all as $id => $item ) {
				$isKeyOK = ( $item->key == $enumKey );
				$isDisabledOK = ( !$item->disabled or $includeDisabled );
				if ( $isKeyOK and $isDisabledOK and empty($result) ) $result = $item;
			}
			// return empty bean (when not found...)
			if ( empty($result) ) {
				$result = ORM::new('enum');
				if ( $result === false ) {
					self::$error = '[Enum::get] '.ORM::error();
					return false;
				}
			}
		// when no key specified or key has wildcard
		// ===> get multiple items
		} else {
			// prepare enum-key for regular expression
			if ( self::hasWildcard($enumKey) ) {
				$enumKeyPattern = $enumKey;
				// unify wildcards
				$enumKeyPattern = str_replace('%', '*', $enumKeyPattern);
				// dedupe wildcards
				while ( strpos($enumKeyPattern, '**') !== false ) $enumKeyPattern = str_replace('**', '*', $enumKeyPattern);
				// escape special characters
				$spChars = str_split('.+?^$[](){}=!<>|:-#');
				foreach ( $spChars as $i => $char ) $enumKeyPattern = str_replace($char, '\\'.$char, $enumKeyPattern);
				// replace wildcard with regex pattern
				$enumKeyPattern = str_replace('*', '(.+)', $enumKeyPattern);
			}
			// check through each item
			// ===> put matched item into container
			foreach ( $all as $id => $item ) {
				$isKeyOK = ( empty($enumKey) or preg_match('/'.$enumKeyPattern.'/', $item->key) );
				$isDisabledOK = ( !$item->disabled or $includeDisabled );
				if ( $isKeyOK and $isDisabledOK ) $result[$id] = $item;
			}
		} // if-enumKey-noWildcard
		// done!
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
	public static function hasWildcard($str) {
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
				<string name="$enumType" />
				<string name="$enumKey" />
				<mixed name="$remarkKey" default="false" comments="return parsed remark when true; return specific var of parsed remark when string" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function remark($enumType, $enumKey, $remarkKey=false) {
		// get specific item (if any)
		$item = self::get($enumType, $enumKey);
		if ( $item === false ) return false;
		// convert language (when necessary)
		$result = class_exists('I18N') ? I18N::convert($item, 'remark') : ( $item->remark ?? '');
		if ( $result === false and class_exists('I18N') ) {
			self::$error = '[Enum::remark] '.I18N::error();
			return false;
		}
		// parse remark & get specific var (when necessary)
		if ( !empty($remarkKey) ) parse_str($result, $parsed);
		if ( $remarkKey === true ) return $parsed;
		if ( !empty($remarkKey) and is_string($remarkKey) ) return $parsed[$remarkKey] ?? null;
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			alias methods to get parsed remark of specific enum item
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<string name="$enumKey" />
			</in>
			<out>
				<structure name="~return~">
					<string name="*" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function remarkArray($enumType, $enumKey) { return self::remark($enumType, $enumKey, true); }
	public static function parseRemark($enumType, $enumKey) { return self::remark($enumType, $enumKey, true); }




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
		foreach ( $beans as $item ) if ( !empty($item->id) ) {
			// convert language (when necessary)
			if ( class_exists('I18N') ) {
				$result[$item->key] = I18N::convert($item, 'value');
				if ( $result[$item->key] === false ) {
					self::$error = '[Enum::toArray] '.I18N::error();
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
				<string name="$enumType" />
				<string name="$enumKey" />
				<boolean name="$returnKeyIfNotFound" optional="yes" default="true" comments="return empty string otherwise" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function value($enumType, $enumKey, $returnKeyIfNotFound=true) {
		// get specific item (if any)
		$item = self::get($enumType, $enumKey);
		if ( $item === false ) return false;
		if ( empty($item->id) and $returnKeyIfNotFound ) return $enumKey;
		// convert language (when necessary)
		if ( class_exists('I18N') ) {
			$result = I18N::convert($item, 'value');
			if ( $result === false ) {
				self::$error = '[Enum::value] '.I18N::error();
				return false;
			}
		} else $result = $item->value;
		// done!
		return $result;
	}


} // class