<?php


use myApp\app\controllers\Controller;
use myApp\app\controllers\TestController;
use myApp\core\Application;

Application::getApp()->router->get("",function (){
    return Application::getApp()->response->View("contact",["id"=>12]);
//    return "kmd";
},[
    \myApp\app\middlewares\auth::class
]);

Application::getApp()->router->get("api/test",[TestController::class,"test1"]);

Application::getApp()->router->get("test/{text}",[Controller::class,"test"]);

Application::getApp()->router->get("contact/{id}",function ($id){
//    dd(Application::getApp()->response->xxx("contact",["id" => $id ]));
//    return Application::getApp()->response->xxx("contact",["id" => $id ]);
});
