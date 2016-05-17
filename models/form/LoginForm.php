<?php

namespace models\form;

use core\bronx\auth\Auth;
use core\bronx\controller\Model;
use models\Users;

class LoginForm extends Model {

	public $login;
	public $password;
	public $rememberMe;

	public function rules() {
		return [
			['login', ['required']],
			['password', ['required']]
		];
	}

	/**
	 * @return array
	 */
	function labels() {
		return [
			'login' => 'Введите логин',
			'password' => 'Введите пароль',
			'signup' => 'Войти',
			'rememberMe' => 'Запомнить меня'
		];
	}

	public function login()
	{
		if($this->validate()) {
			if(filter_var($this->login, FILTER_VALIDATE_EMAIL)) {
				$user = Users::findOne(['email' => $this->login]);
			} else {
				$user = Users::findOne(['login' => $this->login]);
			}
			if(!empty($user)) {
				if(password_verify($this->password, $user->password)) {
					return Auth::getInstance()->login($user, $this->rememberMe == 'on' ? Auth::COOKIE_LIFETIME : 0);
				}
			}
		}
		return false;
	}
}