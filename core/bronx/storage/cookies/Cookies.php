<?php

namespace core\bronx\storage\cookies;

use models\Define;

class Cookies {

	public function set( string $key, $value, int $lifetime = 0 ) {
		if($lifetime == 0) {
			setcookie($key, $value);
		} else {
			setcookie($key, $value, time() + $lifetime, '/', Define::SITE_NAME);
		}
	}

	public function get( string $key ) {
		if(isset($_COOKIE[$key]))
			return $_COOKIE[$key];
		return null;
	}

	public function unset( string $key ) {
		if(isset($_COOKIE[$key])) {
			self::set($key, '', time()-300);
		}
	}
}