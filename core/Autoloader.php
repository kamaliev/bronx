<?php

namespace core;

trait Singleton {
    static private $instance = null;

    private function __construct() { /* ... @return Singleton */ }  // Защищаем от создания через new Singleton
    private function __clone() { /* ... @return Singleton */ }  // Защищаем от создания через клонирование
    private function __wakeup() { /* ... @return Singleton */ }  // Защищаем от создания через unserialize

    static public function getInstance() {
        return
            self::$instance===null
                ? self::$instance = new static()//new self()
                : self::$instance;
    }
}

class Autoloader
{
    use Singleton;

    static private $path = [];

    static public function getPath()
    {
        return self::$path;
    }

    static public function register()
    {
        self::$path = [
            '../controllers/main/',
//            '../controllers/registration/',
//            '../Controller/UserActions/'
        ];
        spl_autoload_register(array('core\autoloader','autoload'));
    }

    static public function autoload( $class ) {

        preg_match('/\\\/', $class, $matches);

        if(count($matches) > 0) {
            $class = str_replace('\\','/',$class);
            $class = '../'.$class;
            try {
                if (file_exists($class . '.php')) {
                    include_once $class . '.php';
                    return;
                } else {
                    throw new \Exception("Класс {$class}.php, не найден!");
                }
            } catch(\Exception $e) {
                echo "{$e->getMessage()}\n";
            }
        } else {
            foreach (self::$path as $path) {
                try {
                    if (file_exists($path . $class . '.php')) {
                        include_once $path . $class . '.php';
                        return;
                    } else {
                        if($path == end(self::$path))
                            throw new \Exception("Класс {$class}.php, не найден!");
                    }
                } catch(\Exception $e) {
                    echo "{$e->getMessage()}\n";
                }
            }
        }
    }
}