<?php

namespace core\bronx\controller;

abstract class Model {

	const PARAM = 0;
	const RULES = 1;

	private $data = false;
	private $valid = false;

	private $checkingResult = [];
	private $predefinedRules = [];
	private $overrideRules = [];
	private $paramValue = '';
	private $paramName = '';

	abstract public function rules();

	public function errorMessages()
	{
		return [
			'email' => 'Введите верный email адрес.',
			'required' => 'Поле не должно быть пустым.',
			'regexp' => 'Введенные данные не верны.'
		];
	}

	/**
	 * @return array
	 */
	abstract function labels();

	public function isData()
	{
		return $this->data;
	}

	/**
	 * @param bool $filter
	 *
	 * @return array
	 */
	public function validateResult($filter = false)
	{
		if($filter)
			return array_filter(array_map(function($v) {
				return array_filter($v, function($v){
					return $v == 0;});}, $this->checkingResult), function($v) {
				return !empty($v);
			});

		return $this->checkingResult;
	}

	public function load($POST)
	{
		if($POST === null)
			return false;

		foreach($POST as $item => $value)
			$this->$item = $value;

		$this->data = true;

		return true;
	}

	public function isValid()
	{
		return $this->valid;
	}

	public function validate()
	{
		$rules = $this->rules();
		for ( $i = 0; $i < count( $rules ); $i ++ ) {
			$this->predefinedRules                    = [ ];
			$this->overrideRules                      = [ ];
			$this->paramValue                         = $this->{$rules[ $i ][ self::PARAM ]};
			$this->paramName                          = $rules[ $i ][ self::PARAM ];
			$this->checkingResult[ $this->paramName ] = [ ];

			$rule  = $rules[ $i ][ self::RULES ];
			$count = count( $rule );
			for ( $c = 0; $c < $count; $c ++ ) {
				if ( isset( $rule[ $c ] ) ) {
					array_push( $this->predefinedRules, $rule[ $c ] );
					unset( $rule[ $c ] );
				}
			}
			$this->overrideRules = $rule;

			if ( ! empty( $this->predefinedRules ) ) {
				$this->predefinedRules( $this->predefinedRules );
			}
			if ( ! empty( $this->overrideRules ) ) {
				$this->overrideRules( $this->overrideRules );
			}
		}

		foreach ( $this->checkingResult as $param => $rule ) {
			foreach ( $rule as $item ) {
				if ( $item == 0 ) {
					$this->valid = false;
					return false;
				}
			}
		}

		$this->valid = true;
		return true;
	}

	public function predefinedRules(array $predefinedRules)
	{
		foreach($predefinedRules as $rule)
		{
			switch ($rule) {
				case 'trim' : {
					$this->{$this->paramName} = trim($this->paramValue);
//					$this->checkingResult[$this->paramName] += [$rule => strcmp($this->paramValue, trim($this->paramValue)) === 0 ? 1 : 0];
					break;
				}
				case 'email' : {
					$this->checkingResult[$this->paramName] += [$rule => filter_var($this->paramValue, FILTER_VALIDATE_EMAIL) ? 1 : 0];
					break;
				}
				case 'required' : {
					$this->checkingResult[$this->paramName] += [$rule => empty($this->paramValue) ? 0 : 1];
					break;
				}
				default : {
					$this->checkingResult[$this->paramName] += [$rule => $this->$rule($this->paramName, $this->paramValue) ? 1 : 0];
				}
			}
		}
	}

	public function overrideRules(array $overrideRules)
	{
		foreach($overrideRules as $rule => $option) {
			switch ($rule) {
				case 'regexp' : {
					$this->checkingResult[$this->paramName] += [$rule => preg_match($option, $this->paramValue) ? 1 : 0];
					break;
				}
			}
		}
	}

}