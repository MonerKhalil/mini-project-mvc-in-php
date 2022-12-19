<?php

namespace myApp\core\http;


use myApp\core\http\response\AbstractResponseFactory;
use myApp\core\http\response\ResponseFactory;
use myApp\core\validation\Validation;

class Request
{
    private Validation $validation;
    private AbstractResponseFactory $response;

    public function __construct($validation,$response)
    {
        $this->validation = $validation;
        $this->response = $response;
    }

    public function validator($rule,$message = []): ResponseFactory|bool
    {
        $validate = $this->validation->make($this->all(),$rule,$message);
        if ($validate->isFails()){
            return $this->response->Json([
                "errors" => [
                    "validation" => $this->validation->getErrors()
                ]
            ]);
        }
        return true;
    }

    public function __get($name)
    {
        return $this->all()[$name] ?? null;
    }

    public function getPath(){
        $path = $this->server('REQUEST_URI') ?? "/";
        $position = strpos($path,"?");
        if ($position===false){
            return $path;
        }
        return substr($path,0,$position);
    }

    public function getMethod(): string
    {
        return strtolower($this->server('REQUEST_METHOD'));
    }

    private function getBody(): array
    {
        $body = [];
        if ($this->getMethod()==='get'){
            foreach ($_GET as $key => $value){
                $body[$key] = filter_input(INPUT_GET,$key,FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->getMethod()==='post'){
            foreach ($_POST as $key => $value){
                $body[$key] = filter_input(INPUT_POST,$key,FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }

    public function all(): array
    {
        return array_merge($this->getBody(),$_FILES);
    }

    public function file(string $filename):UploadedFile{
        if (isset($this->files[$filename])){
            return $this->files[$filename];
        }
        $this->files[$filename] = new UploadedFile($filename);
        return $this->files[$filename];
    }
    public function server($key)
    {
        return $_SERVER[$key] ?? null;
    }
}