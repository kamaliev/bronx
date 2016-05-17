<?php
/**
 * Created by PhpStorm.
 * User: runie
 * Date: 07.04.16
 * Time: 20:57
 */

namespace core\bronx\helpers;

use core\bronx\controller\Model;

class Form extends Element {

	protected $tag = 'form';
	protected $attributes = [
		'method' =>  'post',
		'class' => 'ui form'
	];

	public function open(array $attributes = [ ] ) {

		$this->attributes = array_merge( $this->attributes, [ 'class' => 'ui form warning' ] );
		$this->push('open', parent::open($attributes));
		return $this;
	}

	public function field(string $tag, array $elementAttributes = [], array $divAttributes = [])
	{
		if(isset($this->model->labels()[$elementAttributes['name']])) {
			$label = $this->model->labels()[$elementAttributes['name']];
		} else {
			$label = $elementAttributes['name'];
		}

		$divAttributes = array_merge($divAttributes, ['class' => 'field']);

		switch ($tag) {
			case 'input' : {
				$this->push($elementAttributes['name'], <<<HTML
<div{$this->attrToString($divAttributes)}>
	<label>{$label}</label>
	<input{$this->attrToString($elementAttributes)}>
</div>
HTML
);
				break;
			}
			case 'checkbox' : {
				$elementAttributes = array_merge($elementAttributes, ['type' => 'checkbox']);
				$this->push($elementAttributes['name'], <<<HTML
<div{$this->attrToString($divAttributes)}>
    <div class="ui toggle checkbox">
        <input{$this->attrToString($elementAttributes)}>
        <label>{$label}</label>
    </div>
</div>
HTML
);
				break;
			}
		}
		return $this;
	}

	public function button(array $elementAttributes = [], array $divAttributes = [])
	{
		if(isset($this->model->labels()[$elementAttributes['id']])) {
			$label = $this->model->labels()[$elementAttributes['id']];
		} else {
			$label = $elementAttributes['id'];
		}

		$elementAttributes = array_merge($elementAttributes, ['class' => 'ui button']);
		$divAttributes = array_merge($divAttributes, ['class' => 'submit']);

		$this->push($elementAttributes['id'], <<<HTML
<div{$this->attrToString($divAttributes)}>
    <button{$this->attrToString($elementAttributes)}>{$label}</button>
</div>
HTML
);
		return $this;
	}

}