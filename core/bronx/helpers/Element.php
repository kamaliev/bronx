<?php

namespace core\bronx\helpers;

use core\bronx\controller\Model;

abstract class Element {

	/**
	 * @var Model $model;
	 */
	protected $model;
	protected $tag;
	protected $attributes = [];
	protected $content = '';
	protected $storage = [];

	public function __construct(Model $model) {
		$this->model = $model;
	}

	public function open(array $attributes = [])
	{
		if(isset($attributes) || isset($this->attributes)) {
			$attributes = array_merge($this->attributes, $attributes);
			$attributes = $this->attrToString($attributes);
		}
		return $this->content .= "<{$this->tag}{$attributes}>";
	}

	public function close()
	{
		$this->push('close', "</{$this->tag}>");
	}

	public function getData()
	{
		if(!empty($this->storage)) {
			$this->replace(0,'open', preg_replace('/^(.*?)class="(.*?)"(.*?)$/', '$1class="$2 error"$3', $this->get('open', 0)));
			$filter = $this->model->validateResult(true);

			foreach($this->storage as $k => $v)
			{
				if(isset($filter[$k])) {

					$this->replace(0,$k, preg_replace('/^(.*?)class="(.*?)"(.*?)$/', '$1class="$2 error"$3', $this->get($k, 0)));

					$message = '';

					foreach($filter[$k] as $key => $value) {
						$message .= "<p>{$this->model->errorMessages()[$key]}</p>";
					}

					$this->push($k, <<<HTML
<div class="ui error message">
	{$message}
</div>
HTML
);
				}
			}
		}

		return implode('', array_map(function($v) {
			return implode('', $v);
		}, $this->storage));
	}

	public function attrToString(array $attributes)
	{
		return implode('', array_map( function ($v, $k) { return " $k=\"$v\""; }, $attributes, array_keys($attributes) ));
	}

	public function push(string $key, string $content)
	{
		if(!isset($this->storage[$key])) {
			$this->storage[$key] = [];
		}
		array_push($this->storage[$key], str_replace("\n",'',$content));
	}

	public function add(string $key, string $content) {
		if(array_key_exists($key, $this->storage)) {
			array_push($this->storage[$key], str_replace("\n",'',$content));
		}
	}

	public function replace(int $index, string $key, string $content)
	{
		if(array_key_exists($key, $this->storage)) {
			if(isset($this->storage[$key][$index])) {
				$this->storage[$key][$index] = str_replace("\n",'',$content);
			}
		}
	}

	public function get(string $key, int $index = null)
	{
		if(is_null($index) && isset($this->storage[$key])) {
			return $this->storage[$key];
		}

		if(isset($this->storage[$key][$index])) {
			return $this->storage[$key][$index];
		}

		return null;
	}

}