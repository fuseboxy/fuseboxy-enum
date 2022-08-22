Fuseboxy Enum
=============

Reduce Your DB Complexity


## Installation

#### By Composer

#### Manually


--------------------------------------------------


## GUI

* https://{HOSTNAME}/{APP}/index.php?fuseaction=enum


--------------------------------------------------


## Methods

#### Enum::all ( $type )
````
<fusedoc>
	<description>
		get all items (included disabled) of specific type
		load from cache when available
	</description>
	<io>
		<in>
			<string name="$type" />
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

#### Enum::array ( $type, $keyWithWildcard=null, $includeDisabled=false )
````
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
````

#### Enum::count ( $type, $includeDisabled=false )
````
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
````

#### Enum::exists ( $type, $key )
````
<fusedoc>
	<description>
		check if specific enum exists
	</description>
	<io>
		<in>
			<string name="$type" />
			<string name="$key" />
		</in>
		<out>
			<boolean name="~return~" />
		</out>
	</io>
</fusedoc>
````

#### Enum::first ( $type, $includeDisabled=false )
````
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
````

#### Enum::get ($type, $key=null, $includeDisabled=false )
````
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
````

#### Enum::getAll ( $type )
````
Alias of [Enum::all] method
````

#### Enum::getArray ( $type, $key=null, $includeDisabled=false )
````
Alias of [Enum::array] method
````

#### getFirst ( $type, $includeDisabled=false )
````
Alias of [Enum::first] method
````

#### Enum::getRemark ( $type, $key, $var=false )
````
Alias of [Enum::remark] method
````

#### Enum::getValue ( $type, $key, $returnKeyIfNotFound=true )
````
Alias of [Enum::value] method
````

#### Enum::remarkArray ( $type, $key )
````
Alias of [Enum::remark] method with [$var=true]
````

#### Enum::parseRemark( $type, $key )
````
Alias of [Enum::remark] method with [$var=true]
````

#### Enum::remark ( $type, $key, $var=false )
````
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
````

#### Enum::value ( $type, $key, $returnKeyIfNotFound=true )
````
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
````