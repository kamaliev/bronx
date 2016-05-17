<?php
namespace models;

class Define
{
    const _FRAMEWORK_NAME = "Bronx 2.0 Beta";

    const _DEFAULT_ROUTE = 'ControllerIndex';
    const _DEFAULT_404 = '\controllers\main\ControllerIndex:http404';
    const _VIEW_PATH = '../Views/';

    const REGEXP_PASSWORD = '(?=^.{8,}$)(?=.*\d)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$';
    const REGEXP_LOGIN = '/^[a-z0-9_-]{3,17}$/';

    const SITE_NAME = '2cto.local';
//    const RUSSIAN_MONTH = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

}