<?php
class Enum {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	// alias methods (for backward compatibility)
	public static function getAll   ($type, $key=null)                       { return self::all($type, $key); }
	public static function getArray ($type, $key=null, $all=false)           { return self::array($type, $key, $all); }
	public static function getFirst ($type, $all=false)                      { return self::first($type, $all); }
	public static function getRemark($type, $key)                           { return self::remark($type, $key); }
	public static function getValue ($type, $key, $returnKeyIfNotFound=true) { return self::value($type, $key, $returnKeyIfNotFound); }




	/**
	<fusedoc>
		<description>
			get all items (included disabled) of specific type
		</description>
		<io>
			<in>
			</in>
			<out>
				<structure name="~return~">
					<string name="type" />
					<string name="key" />
					<string name="value" />
					<string name="remark" />
				</object>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($type, $key=null) {
		return self::get($type, $key, true);
	}




	/**
	<fusedoc>
		<description>
			get multiple items as an array
		</description>
		<io>
			<in>
				<string name="$type" />
				<string name="$key" optional="yes" comments="supposed to have wildcard" />
				<boolean name="$all" optional="yes" default="false" comments="include disabled items when true" />
			</in>
			<out>
				<structure name="~return~">
					<string name="~enumKey~" value="~enumValue~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function array($type, $key=null, $all=false) {
		$beans = self::get($type, $key, $all);
		// check if multiple or single
		if ( empty($key) or self::hasWildcard($key) ) {
			return self::toArray($beans);
		} elseif ( !empty($beans->id) ) {
			return array( $beans->key => $beans->value );
		} else {
			return array();
		}
	}




	/**
	<fusedoc>
		<description>
			get first item of specific type
		</description>
		<io>
			<in>
				<string name="$type" />
				<boolean name="$all" optional="yes" default="false" comments="include disabled items when true" />
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
	public static function first($type, $all=false) {
		$beans = self::get($type, null, $all);
		return !empty($beans) ? array_shift($beans) : ORM::new('enum');
	}




	/**
	<fusedoc>
		<description>
			get multiple items by type
			get multiple items by type & key-with-wildcard
			get single item by type & key
		</description>
		<io>
			<in>
				<string name="$type" />
				<string name="$key" optional="yes" example="home-applicance|home-%" />
				<boolean name="$all" optional="yes" default="false" comments="include disabled items when true" />
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
		$order = 'ORDER BY IFNULL(`seq`, 99999), `key` ASC ';
		// get multi records
		if ( empty($key) or self::hasWildcard($key) ) {
			$result = ORM::get('enum', $filter.$order, $filterParam);
		// or single value
		} else {
			$result = ORM::first('enum', $filter.$order, $filterParam);
		}
		// validation
		if ( $result === false ) {
			self::$error = ORM::error();
			return false;
		}
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
				<string name="$str" example="home-%" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	private static function hasWildcard($str) {
		return ( stripos($str, '%') !== false );
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
		$result = self::get($type, $key);
		return $result->remark;
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
		foreach ( $beans as $b ) $result[$b->key] = $b->value;
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
		$result = self::get($type, $key);
		if ( empty($result->id) ) {
			return $returnKeyIfNotFound ? $key : '';
		} else {
			return $result->value;
		}
	}


} // class