<?php
class Enum {


	// local cache container
	private static $cache = array();




	/**
	<fusedoc>
		<description>
			get all items of specific type
			===> including disabled items
			===> load from cache when available
		</description>
		<io>
			<in>
				<string name="$enumType" />
			</in>
			<out>
				<structure name="~return~">
					<object name="~enumID~" type="enum">
						<number name="id" />
						<string name="type" />
						<string name="key" />
						<string name="value" />
						<string name="remark" />
						<boolean name="disabled" />
					</object>
				</object>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($enumType) {
		// load from cache (when available)
		$data = self::cache($enumType);
		if ( !is_null($data) ) return $data;
		// when cache not found
		// ===> load from database
		// ===> put data into local cache
		$filter = ' `type` LIKE ? ORDER BY IFNULL(`seq`, 99999), `key` ASC ';
		$param = array($enumType);
		$data = ORM::get('enum', $filter, $param);
		// put into cache
		self::cache($enumType, $data);
		// done!
		return $data;
	}




	/**
	<fusedoc>
		<description>
			get multiple items as an array
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<structure name="$options">
					<boolean name="useKeyAsValue" optional="yes" default="~returnToGetMethod~" />
					<boolean name="includeDisabled" optional="yes" default="~referToGetMethod~" />
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<string name="~enumKey~" value="~enumValue~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function array($enumType, $options=[]) {
		return self::get($enumType, null, array_merge($options, [ 'returnKeyValuePairs' => true ]));
	}




	/**
	<fusedoc>
		<description>
			get multiple items as an array (with key as value)
		</description>
		<io>
			<in>
				<string name="$enumType" />
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
	public static function arrayWithKeyAsValue($enumType, $includeDisabled=false) {
		$result = self::array($enumType, null, $includeDisabled);
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
			getter & setter of local cache
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
	public static function cache($type=null, $data=null) {
		// getter (all)
		if ( is_null($type) ) return self::$cache;
		// getter (specific)
		if ( is_null($data) ) return self::$cache[$type] ?? null;
		// setter
		self::$cache[$type] = $data;
		return $data;
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
		// find first match
		// ===> return right away
		foreach ( $all as $id => $item ) if ( !$item->disabled or $includeDisabled ) return $item;
		// when no match
		// ===> empty bean (when not found)
		$empty = ORM::new('enum');
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
				<structure name="$options">
					<boolean name="useKeyAsValue" optional="yes" default="false" />
					<boolean name="includeDisabled" optional="yes" default="false" />
					<boolean name="returnKeyValuePairs" optional="yes" default="false" />
				</structure>
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
	public static function get($enumType, $enumKey=null, $options=[]) {
		$result = array();
		// default options
		if ( is_bool($options) ) $options = array('includeDisabled' => $options);
		$options['useKeyAsValue'] = $options['useKeyAsValue'] ?? false;
		$options['includeDisabled'] = $options['includeDisabled'] ?? false;
		$options['returnKeyValuePairs'] = $options['returnKeyValuePairs'] ?? false;
		// load all of this type (from cache)
		$all = self::all($enumType);
		// when key specified & no wildcard
		// ===> get single item
		if ( !empty($enumKey) and !self::hasWildcard($enumKey) ) {
			// return first match (when found in cache)
			foreach ( $all as $id => $item ) {
				$isKeyOK = ( $item->key == $enumKey );
				$isDisabledOK = ( !$item->disabled or $options['includeDisabled'] );
				if ( $isKeyOK and $isDisabledOK and empty($result) ) $result = $item;
			}
			// return empty bean (when not found...)
			if ( empty($result) ) {
				$result = ORM::new('enum');
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
				$isDisabledOK = ( !$item->disabled or $options['includeDisabled'] );
				if ( $isKeyOK and $isDisabledOK ) $result[$id] = $item;
			}
		} // if-enumKey-noWildcard
		// convert (when necessary)
		if ( $options['returnKeyValuePairs'] or $options['useKeyAsValue'] ) $result = self::toKeyValuePairs($result);
		if ( $options['useKeyAsValue'] ) $result = array_combine(array_keys($result), array_keys($result));
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
		// convert language (when necessary)
		$result = class_exists('I18N') ? I18N::convert($item, 'remark') : ( $item->remark ?? '');
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
	public static function toKeyValuePairs($beans) {
		$result = array();
		// go through each item
		foreach ( $beans as $item ) if ( !empty($item->id) ) {
			// convert language (when necessary)
			$result[$item->key] = class_exists('I18N') ? I18N::convert($item, 'value') : $item->value;
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
		if ( empty($item->id) and $returnKeyIfNotFound ) return $enumKey;
		// convert language (when necessary)
		$result = class_exists('I18N') ? I18N::convert($item, 'value') : $item->value;
		// done!
		return $result;
	}


} // class