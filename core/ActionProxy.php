<?php

namespace myApp\core;

use myApp\app\middlewares\Middleware;
use myApp\core\http\response\ResponseFactory;

class ActionProxy
{
    private Loader $loader;
    private array $middlewares;
    private const NEXT = "__NEXT__";

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
        $this->middlewares = [];
    }

    public function action(array $route, array $arg){
        $TempResponse = null;
        $middlewares =  $route["middleware"];
        if (!empty($middlewares)){
            foreach ($middlewares as $middleware){
                $obj = $this->Middleware($middleware);
                if ($obj instanceof Middleware){
                    if (!$this->CheckIsNext($obj)){
                        $TempResponse = $obj->Check(self::NEXT);
                        break;
                    }
                }else{
                    throw new \Exception("the $middleware is not Middleware",400);
                }
            }
        }
        if (is_null($TempResponse)){
            $TempResponse = $this->loader->action($route,$arg);
        }
        if ($TempResponse instanceof ResponseFactory){
            $TempResponse = $TempResponse->renderContent();
        }
        return $TempResponse;
    }

    private function CheckIsNext(Middleware $middleware): bool
    {
        return $middleware->Check(self::NEXT)=== self::NEXT;
    }

    private function Middleware(string $middleware): mixed
    {
        if(!$this->hasMiddleware($middleware)){
            $this->pushMiddleware($middleware);
        }
        return $this->getObjectMiddleware($middleware);
    }

    private function hasMiddleware(string $middleware):bool{
        return array_key_exists($middleware,$this->middlewares);
    }

    private function pushMiddleware(string $middleware){
        $this->middlewares[$middleware] = new $middleware();
    }

    private function getObjectMiddleware(string $controller): mixed
    {
        return $this->middlewares[$controller];
    }

}