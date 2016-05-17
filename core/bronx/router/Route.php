<?php

namespace core\bronx\router;

class Route
{
    protected $path = '';
    protected $controllerMethod = '';
    protected $requestMethod = '';
    protected static $routes = [];

    public function __construct()
    {
        if(func_num_args() > 0)
        {
            $this->setPath(func_get_arg(0));
            $this->setControllerMethod(func_get_arg(1));
            $this->setRequestMethod(func_get_arg(2));
        }
    }

    /**
     * @param $path String (Путь и переменные)
     * @param $class_name String (Название класса)
     * @param $class_method String (Название метода, который нужно вызвать)
     * @param $request_method String (Метод GET или POST)
     */
    public static function addRoute($path, $class_name, $class_method, $request_method)
    {
        array_push(self::$routes, new self($path, $class_name . ':' . $class_method, $request_method));
    }

    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getControllerMethod()
    {
        return $this->controllerMethod;
    }

    /**
     * @param string $controllerMethod
     */
    public function setControllerMethod($controllerMethod)
    {
        $this->controllerMethod = $controllerMethod;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * @param string $requestMethod
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

}