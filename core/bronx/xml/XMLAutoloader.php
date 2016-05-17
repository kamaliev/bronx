<?php

namespace core\bronx\xml;

use phpbrowscap\Exception;

class XMLAutoloader
{

    const FILE = '../config.xml';

    public function createXMLAutoloader($routeArray = [])
    {
        $xml = new \DOMDocument('1.0', 'ISO-8859-15');
        $xml->formatOutput = true;

        $xml_config = $xml->createElement('config');
        $xml_config->setAttribute('time', date('Y-m-d H:i:s', strtotime("+1 day")));
        $xml_route = $xml->createElement('route');
        foreach ($routeArray as $route) {
            $xml_route_path = $xml->createElement('path', $route->getPath());
            $xml_route_cmethod = $xml->createElement('cmethod', $route->getControllerMethod());
            $xml_route_rmethod = $xml->createElement('rmethod', $route->getRequestMethod());
            $xml_route = $xml->createElement('route');
            $xml_route->appendChild($xml_route_path);
            $xml_route->appendChild($xml_route_cmethod);
            $xml_route->appendChild($xml_route_rmethod);
            $xml_config->appendChild($xml_route);
        }
        $xml->appendChild($xml_config);
        $xml->save(self::FILE);
    }

    public function checkXMLUpdate()
    {
        if(file_exists(self::FILE)) {
            $xml = new \SimpleXMLElement(file_get_contents(self::FILE));
            if(strtotime((string)$xml->attributes()->time) < time()) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function getXMLRoutes()
    {
        if(file_exists(self::FILE)) {
            $xml = new \SimpleXMLElement(file_get_contents(self::FILE));
            return $xml->route;
        }
        return false;
    }
}