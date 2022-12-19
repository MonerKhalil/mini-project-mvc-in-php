<?php

namespace myApp\core\http\response;

class Response extends AbstractResponseFactory
{
    public function Json($value,int $code = 200){
        $this->setStatusCode($code);
        return new Json($value);
    }
    public function View($PathView,array $data = [],int $code = 200){
        $this->setStatusCode($code);
        return new View($PathView,$data);
    }
}