<?php

namespace core\bronx\template;

use core\bronx\auth\Auth;
use core\bronx\helpers\Html;

class Tpl
{

	protected static $instance;
	private $twig;
//	private $content = array();

	private function __construct()
	{
		$loader = new \Twig_Loader_Filesystem('../views/');
		$this->twig = new \Twig_Environment( $loader, [ 'cache' => '../cache/twig',
			'debug'       => TRUE,
			'auto_reload' => TRUE,
//			'autoescape' => FALSE
		] );

//		$this->twig->addGlobal('html', new Html());
		$this->twig->addGlobal('auth', Auth::getInstance());

		$this->twig->addExtension( new \Twig_Extension_Debug() );
	}

	static public function getTpl()
	{
		if( !isset( self::$instance ) )
		{
//			$className      = __CLASS__;
			self::$instance = new self();
		}

		return self::$instance->getTwig();
	}

	public function getTwig()
	{
		return $this->twig;
	}

	private function __clone() {}
}