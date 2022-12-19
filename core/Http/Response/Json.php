<?php

namespace myApp\core\http\response;

class Json implements ResponseFactory
{
    private $value;
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function renderContent()
    {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($this->value);
    }
}