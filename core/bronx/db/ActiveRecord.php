<?php

namespace core\bronx\db;

abstract class ActiveRecord extends ActiveQuery {

	protected $_properties;

	public function __set( $name, $value ) {
		$this->_properties[$name] = $value;
	}

	public function __get( $name ) {
		if(isset($this->_properties[$name])) {
			return $this->_properties[$name];
		} else {
			return null;
		}
	}

	public function save()
	{
		if(isset($this->_properties['id']))
		{
			$this->updateRow();
		} else {
			$this->createRow();
		}
	}

}