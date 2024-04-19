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
					<boolean name="includeDisabled" optional="yes" default="~referToGetMethod~" />
					<boolean name="useKeyAsValue" optional="yes" default="~returnToGetMethod~" />
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
				<structure name="$options">
					<boolean name="includeDisabled" optional="yes" default="~referToGetMethod~" />
				</structure>
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function count($enumType, $options=[]) {
		return count(self::get($enumType, null, $options));
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
		return !is_null(self::get($enumType, $enumKey));
	}




	/**
	<fusedoc>
		<description>
			get first item of specific type
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<structure name="$options">
					<boolean name="includeDisabled" optional="yes" default="~referToGetMethod~" />
				</structure>
			</in>
			<out>
				<object name="~return~" type="enum">
					<string name="type" />
					<string name="key" />
					<string name="value" />
					<string name="remark" />
				</object>
			</out>
		</io>
	</fusedoc>
	*/
	public static function first($enumType, $options=[]) {
		$items = self::get($enumType, null, $options);
		return $items[array_key_first($items)] ?? null;
	}




	/**
	<fusedoc>
		<description>
			get first item key of specific type
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<structure name="$options">
					<boolean name="includeDisabled" optional="yes" default="~referToGetMethod~" />
				</structure>
			</in>
			<out>
				<string name="~return~" value="~enumKey~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function firstKey($enumType, $options=[]) {
		$firstItem = self::first($enumType, $options);
		return $firstItem->key ?? null;
	}




	/**
	<fusedoc>
		<description>
			get multiple items by type
			get single item by type & key
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<string name="$enumKey" optional="yes" example="home-applicance|home-%|*-applicance" />
				<structure name="$options">
					<boolean name="includeDisabled" optional="yes" default="false" />
					<boolean name="returnKeyValuePairs" optional="yes" default="false" />
					<boolean name="useKeyAsValue" optional="yes" default="false" />
				</structure>
			</in>
			<out>
				<!-- return : multiple -->
				<structure name="~return~" optional="yes" oncondition="when only {enumType} specified">
					<object name="~id~" type="enum">
						<string name="type" example="PRODUCT_CATEGORY" />
						<string name="key" example="home-appliance" />
						<string name="value" example="Home Appliances" />
						<string name="remark" />
					</object>
				</structure>
				<!-- return : single -->
				<object name="~return~" type="enum" optional="yes" oncondition="when both {enumType & enumKey} specified" comments="return {null} when not found">
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
		// default options
		$options['includeDisabled']     = $options['includeDisabled']     ?? false;
		$options['returnKeyValuePairs'] = $options['returnKeyValuePairs'] ?? false;
		$options['useKeyAsValue']       = $options['useKeyAsValue']       ?? false;
		// validation
		if ( empty($enumType) ) throw new Exception('Enum type is required');
		// load all of this type
		// ===> filter by options
		// ===> convert by options
		$result = self::all($enumType);
		$result = array_filter(array_map(fn($item) => ( !$item->disabled or $options['includeDisabled'] ) ? $item : null, $result));
		if ( $options['returnKeyValuePairs'] or $options['useKeyAsValue'] ) $result = self::toKeyValuePairs($result);
		if ( $options['useKeyAsValue'] ) $result = array_combine(array_keys($result), array_keys($result));
		// when key not specified
		// ===> return multiple items
		if ( empty($enumKey) ) return $result;
		// otherwise
		// ===> return single item (first match)
		// ===> return null when not found
		$result = array_filter(array_map(fn($item) => ( $item->key == $enumKey ) ? $item : null, $result));
		return array_shift($result);
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
				<structure name="$options">
					<boolean name="includeDisabled" optional="yes" default="~referToGetMethod~" />
					<boolean name="returnKeyWhenNotFound" optional="yes" default="true" />
				</structure>
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function value($enumType, $enumKey, $options=[]) {
		// default options
		$options['returnKeyWhenNotFound'] = $options['returnKeyWhenNotFound'] ?? true;
		// load specific item
		$bean = self::get($enumType, $enumKey, $options);
		// return key or null when not found
		if ( is_null($bean) ) return $options['returnKeyWhenNotFound'] ? $enumKey : null;
		// convert language (when necessary)
		return class_exists('I18N') ? I18N::convert($bean, 'value') : $bean->value;
	}


} // class