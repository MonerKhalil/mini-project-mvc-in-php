<?php

namespace myApp\core;

use myApp\core\http\response\ResponseFactory;

class Loader
{
    /**
     * @var array
     */
    private array $controllers=[];


    public function action(array $route, array $arg)
    {
        $value = null;
        if (is_callable($route['callback'])){
            $value = call_user_func_array($route['callback'],$arg);
        }
        if (is_array($route['callback'])){
            $obj = $this->Controller($route['callback'][0]);
            $fn = $route['callback'][1] ?? 'index';
            $value = call_user_func_array([$obj,$fn],$arg);
        }
        return $value;
    }

    /**
     * @param string $controller #: path full Controller
     * @return mixed
     */
    private function Controller(string $controller): mixed
    {
        if(!$this->hasController($controller)){
            $this->pushController($controller);
        }
        return $this->getObjectController($controller);
    }

    /**
     * @param string $controller
     * @return bool
     */
    private function hasController(string $controller):bool{
        return array_key_exists($controller,$this->controllers);
    }

    /**
     * @param string $controller
     * @return void
     */
    private function pushController(string $controller){
        $object = new $controller();
        $this->controllers[$controller] = $object;
    }

    /**
     * @param string $controller
     * @return mixed
     */
    private function getObjectController(string $controller): mixed
    {
        return $this->controllers[$controller];
    }
}