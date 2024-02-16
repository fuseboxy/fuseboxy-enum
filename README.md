Fuseboxy Enum (1.x)
===================

Reduce Your DB Complexity


## Installation

#### By Composer

#### Manually


--------------------------------------------------


## GUI

* https://{HOSTNAME}/{APP}/index.php?fuseaction=enum


--------------------------------------------------


## Methods

#### Enum::all ( $enumType )
````
<fusedoc>
	<description>
		get all items (included disabled) of specific type
		load from cache when available
	</description>
	<io>
		<in>
			<string name="$enumType" />
		</in>
		<out>
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
````

#### Enum::array ( $enumType, $enumKeyWithWildcard=null, $includeDisabled=false )
````
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
````

#### Enum::count ( $enumType, $includeDisabled=false )
````
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
````

#### Enum::exists ( $enumType, $enumKey )
````
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
````

#### Enum::first ( $enumType, $includeDisabled=false )
````
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
````

#### Enum::get ($enumType, $enumKey=null, $includeDisabled=false )
````
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
````

#### Enum::remark ( $enumType, $enumKey, $remarkKey=false )
````
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
````

#### Enum::remarkArray | Enum::parseRemark ( $enumType, $enumKey )
````
<fusedoc>
	<description>
		obtain remark which parsed from query-string to array
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
````

#### Enum::toArray ( $beans )
````
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
````

#### Enum::value ( $type, $key, $returnKeyIfNotFound=true )
````
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
````