<?php

namespace core\bronx\storage\session;

class Session {

	public function set( string $key, $value) {
		if(!isset($_SESSION)){session_start();}
		$_SESSION[$key] = $value;
		session_write_close();
	}

	public function get( string $key ) {
		if(!isset($_SESSION)){session_start();}
		if(!isset($_SESSION) || !isset($_SESSION[$key]))
			return null;
		return $_SESSION[$key];

	}

	public function unset( string $key ) {
		if(!isset($_SESSION)){session_start();}
		if(isset($_SESSION) && isset($_SESSION[$key]))
			unset($_SESSION[$key]);
		return true;
	}
}