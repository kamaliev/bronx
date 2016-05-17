<?php
/**
 * Created by PhpStorm.
 * User: runie
 * Date: 08.04.16
 * Time: 16:27
 */

namespace models;


use core\bronx\db\ActiveRecord;


/**
 * Class Users
 * @package models
 * @property integer $id;
 * @property string $login;
 * @property string $email;
 * @property string $password;
 * @property string $name;
 */
class Users extends ActiveRecord {

	public function login()
	{

	}

	public function rules() {
		// TODO: Implement rules() method.
	}

	/**
	 * @return array
	 */
	function labels() {
		// TODO: Implement labels() method.
	}
}