<?php

namespace controllers\main;

use core\bronx\controller\Controller;
use core\bronx\auth\Auth;
use core\bronx\helpers\Form;
use core\Core;
use models\form\LoginForm;
use models\form\RegistrationForm;

class ControllerMain extends Controller
{

    public static function router()
    {
        parent::router([
            '/' => ['main', 'GET'],
            '/signup/' => ['signup', 'GET|POST'],
            '/logout/' => ['logout', 'GET'],
            '/registration/' => ['registration', 'GET|POST']
        ]);
    }

    protected function actionLogout()
    {
        Auth::getInstance()->logout();
        $this->redirect('/signup');
    }

    protected function actionSignup()
    {
        $model = new LoginForm();
        if($model->load(Core::POST()) && $model->login()) {
            $this->redirect('/registration');
        }

        $form = new Form($model);
        $form->open()->field('input', [
            'type' => 'text',
            'name' => 'login',
        ])->field('input', [
            'type' => 'password',
            'name' => 'password',
        ])->field('checkbox', [
            'name' => 'rememberMe',
            'class' => 'hidden',
            'tabindex' => '0'
        ])->button([
            'type' => 'submit',
            'id' => 'signup'
        ])->close();

        //todo helpers и возврат ошибок
        $this->setContent([
            'form' => $form->getData()
        ]);
    }

    protected function actionRegistration()
    {
        $model = new RegistrationForm();

        if($model->load(Core::POST()) && $model->validate()) {
            $model->save();
        }

        $form = new Form($model);

        $form->open()->field('input', [
                'type' => 'text',
                'name' => 'login',
                'placeholder' => 'Введите логин'
            ])
            ->field('input', [
                'type' => 'text',
                'name' => 'email',
                'placeholder' => 'Введите email'
            ])
            ->field('input', [
                'type' => 'password',
                'name' => 'password',
                'placeholder' => 'Введите пароль'
            ])->button([
                'type' => 'submit',
                'id' => 'reg'
            ]);

        $this->setContent([
            'form' => $form->getData(),
        ]);
    }

    /**
     *  Для закоментированного примера нужно настроить бд
     *  core/bronx/db/DB.php
     *  создать таблицу test и поля id, date, hash
     */
    protected function actionMain()
    {

//        $test = Test::findAll(['hash' => '123']);

//        /**
//         * @var Test $item
//         */
//        foreach ( $test as $item ) {
//            echo $item->id . "\n";
//        }
//
//        $model = new LoginForm();
//
//        $this->setTitle('test');
//        $this->setContent([
//            'name' => 'Проверка!',
//            'model' => $model
//        ]);
    }

}