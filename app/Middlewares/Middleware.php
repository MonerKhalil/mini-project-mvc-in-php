<?php

namespace myApp\app\middlewares;

use myApp\core\Application;

abstract class Middleware
{
    public abstract function Check($next,...$arg);
    public function App(): ?Application
    {
        return Application::getApp();
    }
}