<?php

namespace core\bronx\auth;

use core\bronx\storage\Storage;
use core\Core;
use core\Singleton;

class Auth {

	use Singleton;

	const MODEL_KEY = 'AUTH_USER';
	const MODEL_LIFETIME = 'AUTH_LIFETIME';
	const COOKIE_LIFETIME = 604800; //week

	public function login($model, int $lifetime = 0) {


		Storage::app()->session->set(self::MODEL_KEY, $model);
		Storage::app()->session->set(self::MODEL_LIFETIME, $lifetime);

		if($lifetime > 0)
		{
			Storage::app()->cookies->set(self::MODEL_KEY, $model->id . md5($model->password . $_SERVER['HTTP_USER_AGENT'] . Core::getIP()), $lifetime);
		}

		return true;
	}

	public function isGuest()
	{
		if($this->getUser() === null) {
			return true;
		} else {
			return false;
		}
	}

	public function logout()
	{

		Storage::app()->session->unset(self::MODEL_KEY);
		Storage::app()->cookies->unset(self::MODEL_KEY);
	}

	public function getUser()
	{
		return Storage::app()->session->get(self::MODEL_KEY);
	}

}