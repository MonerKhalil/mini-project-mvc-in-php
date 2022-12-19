<?php

namespace myApp\app\controllers;

use myApp\core\Application;
use myApp\core\database\DataBase;
use myApp\core\exception\NotFoundException;
use myApp\core\http\Request;
use myApp\core\http\response\AbstractResponseFactory;
use myApp\core\validation\Validation;

class Controller
{
    public function response(): AbstractResponseFactory
    {
        return Application::getApp()->response;
    }
    public function request(): Request
    {
        return Application::getApp()->request;
    }
    public function validator(): Validation
    {
        return Application::getApp()->validator;
    }
    public function DB(): DataBase
    {
        return Application::getApp()->DB;
    }

    public function __call(string $name, array $arguments)
    {
        throw new NotFoundException("the method {$name} is not exists in controller ".Controller::class);
    }
}