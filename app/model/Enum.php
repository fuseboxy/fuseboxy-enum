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
		return self::get($enumType, null, array_merge($options, [ 'returnKVP' => true ]));
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
					<boolean name="returnKVP" optional="yes" default="false" comments="return key-value pairs" />
					<boolean name="returnKKP" optional="yes" default="false" comments="return key-key pairs" />
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
		$options['includeDisabled'] = $options['includeDisabled'] ?? false;
		$options['returnKVP'] = $options['returnKVP'] ?? false;
		$options['returnKKP'] = $options['returnKKP'] ?? false;
		// validation
		if ( empty($enumType) ) throw new Exception('Enum type is required');
		// load all of this type
		// ===> filter by options
		// ===> convert by options
		$result = self::all($enumType);
		$result = array_filter(array_map(fn($item) => ( !$item->disabled or $options['includeDisabled'] ) ? $item : null, $result));
		if ( $options['returnKVP'] ) $result = self::toKVP($result);
		if ( $options['returnKKP'] ) $result = self::toKKP($result);
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
			get remark of specific enum item (or parse remark as query string)
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<string name="$enumKey" />
				<structure name="$options">
					<string name="remarkKey" optional="yes" />
					<boolean name="parseRemark" optional="yes" default="force {true} when {remarkKey} specified, otherwise {false}" />
				</structure>
			</in>
			<out>
				<string name="~return~" optional="yes" oncondition="when {parseRemark=false}" />
				<structure name="~return~" optional="yes" oncondition="when {parseRemark=true}">
					<string name="~remarkKey~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function remark($enumType, $enumKey, $options=[]) {
		// when options is string
		// ===> shorthand for {remarkKey} option
		if ( is_string($options) ) $options = array('remarkKey' => $options);
		// default options
		// ===> always parse remark when remark key specified
		$options['remarkKey'] = $options['remarkKey'] ?? null;
		$options['parseRemark'] = !empty($options['remarkKey']) ?: $options['parseRemark'] ?? false;
		// obtain specific enum item
		$enumBean = self::get($enumType, $enumKey);
		// when not found
		// ===> simply return null (or empty array)
		if ( is_null($enumBean) ) return $options['parseRemark'] ? [] : null;
		// obtain remark according to locale (when necessary)
		$enumRemark = class_exists('I18N') ? I18N::convert($enumBean, 'remark') : $enumBean->remark;
		// when {parseRemark} specified
		// ===> parse remark as query string
		if ( $options['parseRemark'] ) parse_str($enumRemark, $enumRemark);
		// when {remarkKey} specified
		// ===> return specific remark item
		if ( !empty($options['remarkKey']) ) return $enumRemark[$options['remarkKey']] ?? null;
		// done!
		return $enumRemark;
	}




	/**
	<fusedoc>
		<description>
			get parsed remark of specific enum item
		</description>
		<io>
			<in>
				<string name="$enumType" />
				<string name="$enumKey" />
			</in>
			<out>
				<structure name="~return~">
					<string name="~remarkKey~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseRemark($enumType, $enumKey) {
		return self::remark($enumType, $enumKey, [ 'parseRemark' => true ]);
	}




	/**
	<fusedoc>
		<description>
			convert array-of-objects (beans) or array-of-strings (key-value pairs) to key-key pairs
		</description>
		<io>
			<in>
				<structure name="$data">
					<object name="~id~" type="enum">
						<string name="type" />
						<string name="key" />
						<string name="value" />
						<string name="remark" />
					</object>
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<string name="~key~" value="~key~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function toKKP($data) {
		if ( empty($data) ) return [];
		$isArrayOfObjects = is_object($data[array_key_first($data)]);
		$enumKeys = $isArrayOfObjects ? array_map(fn($item) => $item->key, $data) : array_keys($data);
		return array_combine($enumKeys, $enumKeys);
	}




	/**
	<fusedoc>
		<description>
			convert array-of-objects (beans) to key-value pairs
		</description>
		<io>
			<in>
				<structure name="$beans">
					<object name="~id~" type="enum">
						<string name="type" />
						<string name="key" />
						<string name="value" />
						<string name="value__{locale}" optional="yes" />
						<string name="remark" />
						<string name="remark__{locale}" optional="yes" />
					</object>
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
	public static function toKVP($beans) {
		$enumKeys = array_map(fn($item, $key) => $item->key ?? $key, $beans, array_keys($beans));
		$enumValues = array_map(fn($item) => ( is_object($item) and class_exists('I18N') ) ? I18N::convert($item, 'value') : ( $item->value ?? null ), $beans);
		return array_combine($enumKeys, $enumValues);
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
		$enumBean = self::get($enumType, $enumKey, $options);
		// return key or null when not found
		if ( is_null($enumBean) ) return $options['returnKeyWhenNotFound'] ? $enumKey : null;
		// convert language (when necessary)
		return class_exists('I18N') ? I18N::convert($enumBean, 'value') : $enumBean->value;
	}


} // class