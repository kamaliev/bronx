<?php

namespace core\bronx\router;

class MatchedRoute
{
    private $controller;
    private $parameters;

    public function __construct($controller, array $parameters = array())
    {
        $this->controller = $controller;
        $this->parameters = $parameters;
        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getDefaultView()
    {
        list($controller, $action) = explode(':', $this->controller); //Разделение на action и Controller
//        return $controller. '/' . str_replace('Action', '', $action) . '.html.twig'; //Возвращение "Папка\(удаление Action)имя метода.html"
        return $controller. '/index.html.twig'; //Возвращение "Папка\(удаление Action)имя метода.html"
    }
}