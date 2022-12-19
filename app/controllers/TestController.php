<?php

namespace myApp\app\controllers;


use Exception;

class TestController extends Controller
{
    public function test1(){
//        $validte = $this->validator()->make($this->request()->all(),[
//            "id" => ["required","numeric"]
//        ]);
//        if ($validte->isFails()){
//            return $validte->getErrors();
//        }
//        echo "sakmsa";
//        return "sakmksa";
//        return 1;
        return $this->response()->Json(["user"=>"moner"],0);
//        return $this->DB()->table("users")->select()->get();
//        return $this->DB()->table("users")->where("name","monertest1")->update([
//            "name" => "hibaaaa",
//        ]);
//        return "dmkdmksdmkdmmkds";
    }
}