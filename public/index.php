<?php

/* Bronx v2-beta */

use core\Core;

//production
//display_errors = Off
//display_startup_errors = Off
//error_reporting = E_ALL
//log_errors = On

echo '2cto project. Status: building. Contact: runie [at] mail.ru';
exit;

defined('BRONX_DEBUG') or define('BRONX_DEBUG', true);

if(BRONX_DEBUG == 1) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('display_startup_errors', 'On');
    ini_set("log_errors", 1);
    ini_set("error_log", "../Logs/php-error.log");
}

//ini_set('session.save_handler', 'memcached');
ini_set('session.save_path', '127.0.0.1:11211');
ini_set('session.cookie_domain', '.2cto.local' );

date_default_timezone_set('Europe/Moscow');

header('Content-Type: text/html; charset=utf-8');
putenv('LC_ALL=ru_RU.utf8');
setlocale(LC_ALL, 'ru_RU.utf8');

include_once '../core/Autoloader.php';
include_once '../libs/Twig/Autoloader.php';
include_once '../libs/phpmailer/PHPMailerAutoload.php';
include_once '../libs/phpbrowscap/Browscap.php';

\Twig_Autoloader::register();
\Core\Autoloader::register();

$core = new Core();
$core->view();

if(BRONX_DEBUG == 1)
    echo 'Time: ' . round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 4) . ' мсек.<br>';