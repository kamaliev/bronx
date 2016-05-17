<?php

namespace core;

use core\bronx\auth\Auth;
use core\bronx\controller\Controller;
use core\bronx\storage\cookies\Cookies;
use core\bronx\storage\memcached\Memcached;
use core\bronx\router\MatchedRoute;
use core\bronx\router\Route;
use core\bronx\router\Router;
use core\bronx\storage\session\Session;
use core\bronx\storage\Storage;
use core\bronx\template\Tpl;
use core\bronx\xml\XMLAutoloader;
use models\Define;
use models\Users;
use UnexpectedValueException;

class Core {

	private $class = null;
	private $action = null;

	//test

	/**
	 * @var Router $router
	 */
	private $router;

	/**
	 * @var MatchedRoute $route
	 */
	private $route;
	private $tpl;

	public function __construct() {

		$this->router = new Router( Core::GET_HTTP_HOST() );
		$this->tpl    = Tpl::getTpl();

		if ( $router = Memcached::getInstance()->get( 'getXMLRoutes' ) ) {
			$this->router = $router;
		} else {
			$xml = new XMLAutoloader();
			if ( $xml->checkXMLUpdate() ) {
				foreach ( Autoloader::getPath() as $path ) {
					foreach ( scandir( $path ) as $item ) {
						if ( strcmp( '.', $item ) != 0 && strcmp( '..', $item ) != 0 && preg_match( '/.php/', $item ) ) {
							$namespace = str_replace( '..', '', $path );
							$item      = str_replace( '/', '\\', $namespace . str_replace( '.php', '', $item ) );
							$item::router();
						}
					}
				}
				$xml->createXMLAutoloader( Route::getRoutes() );
			}
			foreach ( $xml->getXMLRoutes() as $item ) {
				$this->router->add(
					(string) $item->path, (string) $item->cmethod,
					(string) $item->rmethod
				);
			}
			Memcached::getInstance()->set(
				'getXMLRoutes', $this->router, 5
			);
		}

		$this->run();
	}

	public function run() {

		if(Auth::getInstance()->isGuest()) {
			if(($cookie = Storage::app()->cookies->get(Auth::MODEL_KEY)) != null) {
				$id = substr($cookie, 0, -32);
				if(!empty($model = Users::findOne(['id' => $id]))) {
					$md5 = substr($cookie, -32);
					if(strcmp($md5, md5($model->password . $_SERVER['HTTP_USER_AGENT'] . Core::getIP())) === 0) {
						Auth::getInstance()->login($model, Auth::COOKIE_LIFETIME);
					}
				}
			}
		}

		$this->route = $this->router->match( Core::GET_METHOD(), Core::GET_PATH_INFO() );
		if ( null == $this->route ) {
			$this->route = new MatchedRoute( Define::_DEFAULT_404 );
		}
		list( $this->class, $this->action ) = explode(
			':', $this->route->getController(), 2
		);

	}

	public function view() {
		/**
		 * Проверяю на существование объекта, если был передан параметр $this в методе, то проверка вернет истинное значение
		 * иначе вернет результат работы метода, $this не передается если используется для JSON данных и AJAX запросов
		 */
		$class = $this->getClass();

		/**
		 * @noinspection PhpUndefinedMethodInspection
		 * @var Controller $controller
		 */
		$controller = new $class(
			$this->getAction(), $this->route->getParameters()
		);

		$controller->setTemplate($controller->className($this->getClass()) . '\\' . mb_strtolower(str_replace('action','',$this->getAction())) . '.html.twig');

		/** @noinspection PhpUndefinedMethodInspection */
		if ( ! empty( $controller->getContent() ) ) {
//			print $this->tpl->render(
//				'index.html.twig', $this->getMainContent( $controller )
//			);
			print $this->tpl->render($controller->getTemplate(), $controller->getContent());
		}
	}

	static public function _GET( $key ) {
		return isset( $_GET[ $key ] ) ? $_GET[ $key ] : null;
	}

	static public function _POST( $key ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : null;
	}

	static public function IS_POST() {
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	static public function POST()
	{
		if(self::IS_POST())
			return $_POST;
		return null;
	}

	static public function GET_METHOD() {
		$method = $_SERVER['REQUEST_METHOD'];
		if ( Core::IS_POST() ) {
			if ( isset( $_SERVER['X-HTTP-METHOD-OVERRIDE'] ) ) {
				$method = strtoupper( $_SERVER['X-HTTP-METHOD-OVERRIDE'] );
			}
		}

		return $method;
	}

	static public function _e( $str ) {
		return htmlspecialchars( $str, ENT_QUOTES, 'UTF-8' );
	}

	static public function _d( $str, $default ) {
		return $str ? Core::_e( $str ) : Core::_e( $default );
	}

	static public function dd( $value ) {
		var_dump( $value );
		die();
	}

	static public function IS_HTTPS() {
		return isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off';
	}

	static public function GET_HTTP_HOST() {
		$host = Core::IS_HTTPS() ? 'https://' : 'http://';
		$host .= Core::GET_HOST();

		return $host;
	}

	static public function GET_HOST() {
		$host = $_SERVER['HTTP_HOST'];
		$host = strtolower( preg_replace( '/:\d+$/', '', trim( $host ) ) );
		if ( $host && ! preg_match( '/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host ) ) {
			throw new UnexpectedValueException(
				sprintf( 'Invalid Host "%s"', $host )
			);
		}

		return $host;
	}

	static public function GET_PATH_INFO( $baseUrl = null ) {
		static $pathInfo;
		if ( ! $pathInfo ) {
			$pathInfo = $_SERVER['REQUEST_URI'];
			if ( ! $pathInfo ) {
				$pathInfo = '/';
			}
			$schemeAndHttpHost = Core::IS_HTTPS() ? 'https://' : 'http://';
			$schemeAndHttpHost .= $_SERVER['HTTP_HOST'];
			if ( strpos( $pathInfo, $schemeAndHttpHost ) === 0 ) {
				$pathInfo = substr( $pathInfo, strlen( $schemeAndHttpHost ) );
			}
			if ( $pos = strpos( $pathInfo, '?' ) ) {
				$pathInfo = substr( $pathInfo, 0, $pos );
			}
			if ( null != $baseUrl ) {
				$pathInfo = substr( $pathInfo, strlen( $pathInfo ) );
			}
			if ( ! $pathInfo ) {
				$pathInfo = '/';
			}
		}

		return $pathInfo;
	}

	static public function getIP() {
		global $REMOTE_ADDR;
		global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
		global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;
		global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

		// Get some server/environment variables values
		if ( empty( $REMOTE_ADDR ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['REMOTE_ADDR'] ) ) {
					$REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
				} else {
					if ( ! empty( $HTTP_SERVER_VARS )
					     && isset( $HTTP_SERVER_VARS['REMOTE_ADDR'] )
					) {
						$REMOTE_ADDR = $HTTP_SERVER_VARS['REMOTE_ADDR'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['REMOTE_ADDR'] )
						) {
							$REMOTE_ADDR = $HTTP_ENV_VARS['REMOTE_ADDR'];
						} else {
							if ( @getenv( 'REMOTE_ADDR' ) ) {
								$REMOTE_ADDR = getenv( 'REMOTE_ADDR' );
							}
						}
					}
				}
			}
		} // end if
		if ( empty( $HTTP_X_FORWARDED_FOR ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['HTTP_X_FORWARDED_FOR'] ) ) {
					$HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
				} else {
					if ( ! empty( $HTTP_SERVER_VARS )
					     && isset( $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'] )
					) {
						$HTTP_X_FORWARDED_FOR
							= $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'] )
						) {
							$HTTP_X_FORWARDED_FOR
								= $HTTP_ENV_VARS['HTTP_X_FORWARDED_FOR'];
						} else {
							if ( @getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
								$HTTP_X_FORWARDED_FOR = getenv(
									'HTTP_X_FORWARDED_FOR'
								);
							}
						}
					}
				}
			}
		} // end if
		if ( empty( $HTTP_X_FORWARDED ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
				$HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['HTTP_X_FORWARDED'] ) ) {
					$HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
				} else {
					if ( ! empty( $HTTP_SERVER_VARS )
					     && isset( $HTTP_SERVER_VARS['HTTP_X_FORWARDED'] )
					) {
						$HTTP_X_FORWARDED
							= $HTTP_SERVER_VARS['HTTP_X_FORWARDED'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['HTTP_X_FORWARDED'] )
						) {
							$HTTP_X_FORWARDED
								= $HTTP_ENV_VARS['HTTP_X_FORWARDED'];
						} else {
							if ( @getenv( 'HTTP_X_FORWARDED' ) ) {
								$HTTP_X_FORWARDED = getenv( 'HTTP_X_FORWARDED' );
							}
						}
					}
				}
			}
		} // end if
		if ( empty( $HTTP_FORWARDED_FOR ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
				$HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['HTTP_FORWARDED_FOR'] ) ) {
					$HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
				} else {
					if ( ! empty( $HTTP_SERVER_VARS )
					     && isset( $HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'] )
					) {
						$HTTP_FORWARDED_FOR
							= $HTTP_SERVER_VARS['HTTP_FORWARDED_FOR'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['HTTP_FORWARDED_FOR'] )
						) {
							$HTTP_FORWARDED_FOR
								= $HTTP_ENV_VARS['HTTP_FORWARDED_FOR'];
						} else {
							if ( @getenv( 'HTTP_FORWARDED_FOR' ) ) {
								$HTTP_FORWARDED_FOR = getenv(
									'HTTP_FORWARDED_FOR'
								);
							}
						}
					}
				}
			}
		} // end if
		if ( empty( $HTTP_FORWARDED ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['HTTP_FORWARDED'] ) ) {
				$HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['HTTP_FORWARDED'] ) ) {
					$HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
				} else {
					if ( ! empty( $HTTP_SERVER_VARS )
					     && isset( $HTTP_SERVER_VARS['HTTP_FORWARDED'] )
					) {
						$HTTP_FORWARDED = $HTTP_SERVER_VARS['HTTP_FORWARDED'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['HTTP_FORWARDED'] )
						) {
							$HTTP_FORWARDED = $HTTP_ENV_VARS['HTTP_FORWARDED'];
						} else {
							if ( @getenv( 'HTTP_FORWARDED' ) ) {
								$HTTP_FORWARDED = getenv( 'HTTP_FORWARDED' );
							}
						}
					}
				}
			}
		} // end if
		if ( empty( $HTTP_VIA ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['HTTP_VIA'] ) ) {
				$HTTP_VIA = $_SERVER['HTTP_VIA'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['HTTP_VIA'] ) ) {
					$HTTP_VIA = $_ENV['HTTP_VIA'];
				} else {
					if ( ! empty( $HTTP_SERVER_VARS )
					     && isset( $HTTP_SERVER_VARS['HTTP_VIA'] )
					) {
						$HTTP_VIA = $HTTP_SERVER_VARS['HTTP_VIA'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['HTTP_VIA'] )
						) {
							$HTTP_VIA = $HTTP_ENV_VARS['HTTP_VIA'];
						} else {
							if ( @getenv( 'HTTP_VIA' ) ) {
								$HTTP_VIA = getenv( 'HTTP_VIA' );
							}
						}
					}
				}
			}
		} // end if
		if ( empty( $HTTP_X_COMING_FROM ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['HTTP_X_COMING_FROM'] ) ) {
				$HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['HTTP_X_COMING_FROM'] ) ) {
					$HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
				} else {
					if ( ! empty( $HTTP_SERVER_VARS )
					     && isset( $HTTP_SERVER_VARS['HTTP_X_COMING_FROM'] )
					) {
						$HTTP_X_COMING_FROM
							= $HTTP_SERVER_VARS['HTTP_X_COMING_FROM'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['HTTP_X_COMING_FROM'] )
						) {
							$HTTP_X_COMING_FROM
								= $HTTP_ENV_VARS['HTTP_X_COMING_FROM'];
						} else {
							if ( @getenv( 'HTTP_X_COMING_FROM' ) ) {
								$HTTP_X_COMING_FROM = getenv(
									'HTTP_X_COMING_FROM'
								);
							}
						}
					}
				}
			}
		} // end if
		if ( empty( $HTTP_COMING_FROM ) ) {
			if ( ! empty( $_SERVER ) && isset( $_SERVER['HTTP_COMING_FROM'] ) ) {
				$HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
			} else {
				if ( ! empty( $_ENV ) && isset( $_ENV['HTTP_COMING_FROM'] ) ) {
					$HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
				} else {
					if ( ! empty( $HTTP_COMING_FROM )
					     && isset( $HTTP_SERVER_VARS['HTTP_COMING_FROM'] )
					) {
						$HTTP_COMING_FROM
							= $HTTP_SERVER_VARS['HTTP_COMING_FROM'];
					} else {
						if ( ! empty( $HTTP_ENV_VARS )
						     && isset( $HTTP_ENV_VARS['HTTP_COMING_FROM'] )
						) {
							$HTTP_COMING_FROM
								= $HTTP_ENV_VARS['HTTP_COMING_FROM'];
						} else {
							if ( @getenv( 'HTTP_COMING_FROM' ) ) {
								$HTTP_COMING_FROM = getenv( 'HTTP_COMING_FROM' );
							}
						}
					}
				}
			}
		} // end if

		// Gets the default ip sent by the user
		if ( ! empty( $REMOTE_ADDR ) ) {
			$direct_ip = $REMOTE_ADDR;
		}

		// Gets the proxy ip sent by the user
		$proxy_ip = '';
		if ( ! empty( $HTTP_X_FORWARDED_FOR ) ) {
			$proxy_ip = $HTTP_X_FORWARDED_FOR;
		} else {
			if ( ! empty( $HTTP_X_FORWARDED ) ) {
				$proxy_ip = $HTTP_X_FORWARDED;
			} else {
				if ( ! empty( $HTTP_FORWARDED_FOR ) ) {
					$proxy_ip = $HTTP_FORWARDED_FOR;
				} else {
					if ( ! empty( $HTTP_FORWARDED ) ) {
						$proxy_ip = $HTTP_FORWARDED;
					} else {
						if ( ! empty( $HTTP_VIA ) ) {
							$proxy_ip = $HTTP_VIA;
						} else {
							if ( ! empty( $HTTP_X_COMING_FROM ) ) {
								$proxy_ip = $HTTP_X_COMING_FROM;
							} else {
								if ( ! empty( $HTTP_COMING_FROM ) ) {
									$proxy_ip = $HTTP_COMING_FROM;
								}
							}
						}
					}
				}
			}
		} // end if... else if...

		// Returns the true IP if it has been found, else FALSE
		if ( empty( $proxy_ip ) ) {
			// True IP without proxy
			/** @noinspection PhpUndefinedVariableInspection */
			return $direct_ip;
		} else {
			$is_ip = preg_match( '^([0-9]{1,3}\.){3,3}[0-9]{1,3}', $proxy_ip, $regs );
			if ( $is_ip && ( count( $regs ) > 0 ) ) {
				// True IP behind a proxy
				return $regs[0];
			} else {
				// Can't define IP: there is a proxy but we don't have
				// information about the true IP
				return false;
			}
		} // end if... else...
	}

	/**
	 * @return null
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * @param null $class
	 */
	public function setClass( $class ) {
		$this->class = $class;
	}

	/**
	 * @return null
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @param null $action
	 */
	public function setAction( $action ) {
		$this->action = $action;
	} // end of the 'PMA_getIp()' function


}