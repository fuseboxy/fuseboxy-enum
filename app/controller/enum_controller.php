<?php
F::redirect('auth', !Auth::user());
F::error('Forbidden', !Auth::userInRole('SUPER,ADMIN'));


// default type
$arguments['type'] = $arguments['type'] ?? ( ORM::count('enum') ? ORM::first('enum', 'ORDER BY type')->type : '' );


// config
$scaffold = array(
	'beanType' => 'enum',
	'retainParam' => array('type' => $arguments['type']),
	'editMode' => 'inline',
	'allowDelete' => Auth::userInRole('SUPER'),
	'layoutPath' => F::appPath('view/enum/layout.php'),
	'listFilter' => array('type = ?', array($$arguments['type'])),
	'listOrder' => 'ORDER BY IFNULL(seq, 9999), `key` ',
	'listField' => array(
		'id' => '60',
		'key|type' => '15%',
		call_user_func(function(){
			$fields = array();
			// all available locale
			$locales = class_exists('I18N') ? I18N::localeAll() : ['en'];
			// field with multi-language
			foreach ( ['value','remark'] as $fieldName ) {
				foreach ( $locales as $localeName ) {
					if ( $localeName == 'en' ) $fields[] = $fieldName;
					else $fields[] = $fieldName.'__'.str_replace('-', '_', $localeName);
				}
			}
			// done!
			return implode('|', $fields);
		}) => '50%',
		'seq' => '70'
	),
	'fieldConfig' => array_merge([
		'type' => array('placeholder' => true, 'readonly' => !Auth::userInRole('SUPER'), 'default' => $arguments['type']),
		'key' => array('placeholder' => true),
		'seq' => array('placeholder' => true),
	], call_user_func(function(){
		$fields = array();
		// all available locale
		$locales = class_exists('I18N') ? I18N::localeAll() : ['en'];
		// field with multi-language
		foreach ( $locales as $localeName ) {
			$fields[ ( $localeName == 'en' ) ? 'value' : 'value__'.str_replace('-', '_', $localeName) ] = array(
				'label' => ( $localeName == 'en' ),
				'placeholder' => ( count($locales) == 1 ),
				'inline-label' => ( count($locales) > 1 ) ? ('<div style="width: 2.5rem">'.strtoupper($localeName).'</div>') : false,
			);
			$fields[ ( $localeName == 'en' ) ? 'remark' : 'remark__'.str_replace('-', '_', $localeName) ] = array(
				'format' => 'textarea',
				'label' => ( $localeName == 'en' ),
				'placeholder' => ( count($locales) == 1 ),
				'inline-label' => ( count($locales) > 1 ) ? ('<div style="width: 2.5rem">'.strtoupper($localeName).'</div>') : false,
				'pre-help' => ( count($locales) > 1 and $localeName == 'en' ) ? '<small class="d-block mt-3">Remark</small>' : false,
				'style' => 'height: 5rem',
			);
		}
		// done!
		return $fields;
	})),
	'writeLog' => class_exists('Log'),
);


// component
include F::appPath('controller/scaffold_controller.php');