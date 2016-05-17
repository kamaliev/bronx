<?php

namespace core\bronx\storage;

use core\bronx\storage\cookies\Cookies;
use core\bronx\storage\memcached\Memcached;
use core\bronx\storage\session\Session;
use core\Singleton;

/**
 * Class Storage
 * @package core\bronx\storage
 * @property Session $session;
 * @property Memcached $memcached;
 * @property Cookies $cookies;
 */
class Storage {

	use Singleton;

	public function __get( $name ) {
		switch ( $name ) {
			case 'session' : {
				return new Session();
			}
			case 'memcahed' : {
				return Memcached::getInstance();
			}
			case 'cookies' : {
				return new Cookies();
			}
		}
		return null;
	}

	static public function app() {
		return
			self::$instance===null
				? self::$instance = new static()//new self()
				: self::$instance;
	}

}