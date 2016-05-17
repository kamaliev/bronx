<?php

namespace modules;

use core\Core;
use core\Tpl;
use phpbrowscap\Browscap;

class CSRF
{
    public static function setToken()
    {
//        $bc = new Browscap('../Cache/Browscap');
//        $current_browser = $bc->getBrowser();

//        $token = md5(date("Ymd") . $current_browser->browser_name .  $current_browser->Version . $current_browser->Platform . $current_browser->Browser_Bits);
        $token = md5(date("Ymd") . time());
        Session::setCookie('token',$token);
        Core::setJSCode(Tpl::getTpl()->render('Controller/Index/js/csrf.js.twig',['csrf_token' => $token]));
    }

    public static function getToken()
    {
        return Session::getCookie('token');
    }

    public static function validateToken()
    {
        if(isset($_POST['token'])) {
            if (self::getToken()) {
                if (self::getToken() == $_POST['token']) {
                    return true;
                }
                return false;
            }
        }
        return false;
    }
}