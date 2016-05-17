<?php

namespace core\bronx\helpers;

use core\bronx\controller\Model;

class Html {

	public function form()
	{
		return new Form();
	}

	public function input(Model $model, $element)
	{
		return (new Input($model, $element))->open();
	}

	public function filed(Model $model, $element)
	{

	}
}