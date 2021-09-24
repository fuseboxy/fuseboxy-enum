<?php
if ( !class_exists('Model_Enum') ) class Model_Enum extends RedBean_SimpleModel {


	// get parsed remark
	public function remark($key=null) {
		parse_str($this->bean->remark, $parsed);
		return empty($key) ? $parsed : ( $parsed[$key] ?? null );
	}


} // class