<?php

namespace myApp\core;

use Exception;
use myApp\core\database\DataBase;
use myApp\core\http\Request;
use myApp\core\http\response\Response;
use myApp\core\validation\Validation;

class Application
{
    public FileSystem $fileSystem;
    public Request $request;
    public Router $router;
    public Response $response;
    private Loader $loader;
    public DataBase $DB;
    public Validation $validator;
    private static $app = null;

    private function __construct()
    {
        try {
            $this->fileSystem = new FileSystem(dirname(__DIR__));
            $this->HelperFunctions();
            $this->validator = new Validation();
            $this->loader = new Loader();
            $this->response = new Response();
            $this->request = new Request($this->validator,$this->response);
            $this->router = new Router($this->request,$this->loader);
            $this->DB = new DataBase();
        }catch (Exception $exception){
            if (str_starts_with($this->request->getPath(),'/api')){
                $this->response->Json(["errors" => [
                    "exception" => $exception::class,
                    "message" => $exception->getMessage()
                ]],$exception->getCode());
                exit;
            }
            else{
                dd('error : '.$exception->getMessage(),true);
            }
        }
    }

    public static function getApp()
    {
        if (is_null(self::$app)){
            self::$app = new Application();
        }
        return self::$app;
    }

    public function runApp(){
        $this->fileSystem->Require($this->fileSystem->getDirRoot()."/app/Routes.php");
        echo $this->router->resolve();
        exit;
    }

    public function getConfig(){
        return $this->fileSystem->Require($this->fileSystem->getDirRoot()."/core/config.php");
    }

    private function HelperFunctions(){
        $this->fileSystem->Require($this->fileSystem->getDirRoot().'/core/helpersFun.php');
    }

}