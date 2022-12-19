<?php

namespace myApp\core\http\response;

abstract class AbstractResponseFactory
{
    public function setStatusCode(int $code){
        http_response_code($code);
    }
    abstract public function Json($value,int $code = 0);
    abstract public function View($PathView,array $data = []);
}