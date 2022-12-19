<?php

namespace myApp\core;

use myApp\core\exception\NotFoundException;
use myApp\core\http\Request;

class Router
{
    private Request $request;
    private Loader $loader;
    private ActionProxy $proxy;
    private array $routes=[];

    public function __construct(Request $request,Loader $loader)
    {
        $this->request = $request;
        $this->loader = $loader;
        $this->proxy = new ActionProxy($loader);
    }

    private function addRoute($path,$callback,$method,array $middlewares =[]){
        $Route = [
            "url_pattern" => $this->generatePattern($path),
            "callback" => $callback,
            "method" => strtolower($method),
            "middleware" => $middlewares,
        ];
        $this->routes[] = $Route;
    }

    public function get(string $path,$callback,array $middlewares =[]){
        $this->addRoute($path,$callback,'get',$middlewares);
    }

    public function post(string $path,$callback,array $middlewares =[]){
        $this->addRoute($path,$callback,'post',$middlewares);
    }

    public function redirectTo($path){
        header("location:".$path);
        exit;
    }

    public function resolve(){
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $route = $this->checkRoute($method,$path);
        $arg = $this->getArguments($route['url_pattern'])??[];
        return $this->proxy->action($route,$arg);
    }

    private function checkRoute($method,$path){
        foreach ($this->routes as $route){
            if ($this->isMatching($route['url_pattern'])){
                if ($this->request->getMethod()!==$route['method']){
                    throw new NotFoundException("the Current Route is Not {$route['method']}.");
                }
                return $route;
            }
        }
        throw new NotFoundException("Route is Not Found !!");
    }

    private function generatePattern(string $url): string
    {
        $tempPath = str_replace(["/","\\","//"],"/",'/'.$url);
        $pattern = "#^";
        $pattern .= str_replace(["{text}","{id}"],["([a-zA-Z0-9-]+)","([0-9]+)"],$tempPath);
        $pattern .= "$#";
        return $pattern;
    }

    private function isMatching($pattern): bool|int
    {
        return preg_match($pattern,$this->request->getPath());
    }

    private function getArguments($pattern): array
    {
        preg_match($pattern,$this->request->getPath(),$matches);
        array_shift($matches);
        return $matches;
    }
}