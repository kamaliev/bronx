<?php
namespace core\bronx\controller;


use core\bronx\router\Route;
use core\bronx\template\Tpl;
use core\Core;

abstract class Controller
{

	const CLASS_METHOD = 0;
	const REQUEST_METHOD = 1;
	const PREFIX_METHOD = 'action';

	const COOKIE_USER_ID = 0;
	const COOKIE_USER_IP = 1;

	private $content = [];

	private $template = [];

	protected $method;
	protected $params = null;


	/**
	 * В функции описывается роутинг относящийся к классу
     */
	public static function router()
	{
		if(func_num_args() > 0) {;
			$class = get_called_class();
			foreach(func_get_arg(0) as $path => $item) {
				Route::addRoute($path, $class, self::PREFIX_METHOD . ucfirst($item[self::CLASS_METHOD]), $item[self::REQUEST_METHOD]);
			}
		} else {
			echo 'Error no routing in class: ' . get_called_class() . '<br>';
			return;
		}
	}

	public function __construct($controller = null, $params = null)
	{
		$this->$controller($params);
	}

	public function className(string $path)
	{
//		$path = get_called_class();
		$path = rtrim( str_replace( '\\', '/', $path ), '/\\' );
		if ( ( $pos = mb_strrpos( $path, '/' ) ) !== false ) {
			return mb_strtolower(str_replace('Controller','',mb_substr( $path, $pos + 1 )));
		}
	}


	/**
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template['template'];
	}

	/**
	 * @param $template array
	 */
	public function setTemplate($template)
	{
		if(isset($this->template['template'])) {
			$this->template = array_replace($this->template, ['title' => $template]);
		} else {
			$this->template = array_merge(['template' => $template], $this->template);
		}
	}

	/**
	 * @return null
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @param null $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}


	public function setTitle($title)
	{
		if(isset($this->content['title'])) {
			$this->content = array_replace($this->content, ['title' => $title]);
		} else {
			$this->content = array_merge(['title' => $title], $this->content);
		}
	}

	public function render($tpl, $arr = [], $noClass = false)
	{
		return Tpl::getTpl()->render($noClass ? $tpl . '.twig' : get_called_class() . '/' . $tpl . '.twig',$arr);
	}

	public function http404()
	{
		$this->setTitle('Страница не найдена');
		$this->setTemplate('index/404.html.twig');
	}

	public function getTitle()
	{
		return $this->content['title'];
	}

	public function setContent($array)
	{
		$this->content = array_merge($array, $this->content);
	}

	public function getContent()
	{
		return $this->content;
	}

	protected function __clone()
	{
		return $this;
	}

	public function redirect( $url, $http_host = true ) {
		header( 'HTTP/1.1 302 OK' );
		header(
			'Location: http://' . ( $http_host ? $_SERVER['HTTP_HOST']
			                                     . $url : $url )
		);
		exit;
	}

	public function refresh($url = null, $timer = 0, $http_host = true )
	{
		if($url === null)
			$url = $_SERVER['REQUEST_URI'];
		header( 'HTTP/1.1 200 OK' );
		header(
			'Refresh: ' . $timer . '; URL=http://' . ( $http_host
				? $_SERVER['HTTP_HOST'] . $url : $url )
		);
		exit;
	}
}