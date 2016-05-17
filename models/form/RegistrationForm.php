<?php

namespace models\form;

use core\bronx\controller\Model;
use models\Users;

class RegistrationForm extends Model {

	public $login;
	public $email;
	public $password;

	public function rules() {
		return [
			['login', ['trim', 'required', 'ruleLogin']],
			['email', ['trim', 'email', 'required', 'ruleEmail']],
			['password', ['regexp' => '/^[\d\w\S]{6,}$/', 'required', 'trim']],
		];
	}

	protected function ruleLogin($name, $value)
	{
		$user = Users::findOne(['login' => $value]);
		if(empty($user))
			return true;
		else
			return false;
	}

	protected function ruleEmail($name, $value)
	{
		$user = Users::findOne(['email' => $value]);
		if(empty($user))
			return true;
		else
			return false;
	}

	function labels() {
		return [
			'login' => 'Введите логин',
			'password' => 'Введите пароль',
			'email' => 'Введите email',
			'reg' => 'Зарегистрироваться',
		];
	}

	public function errorMessages() {
		return array_merge(parent::errorMessages(), [
			'ruleLogin' => 'Такой логин уже существует.',
			'ruleEmail' => 'Такой email уже существует.'
		]);
	}

	public function save()
	{
		$user = new Users();
		$user->login = $this->login;
		$user->email = $this->email;
		$user->password = password_hash($this->password, PASSWORD_DEFAULT);
		$user->save();
	}
}