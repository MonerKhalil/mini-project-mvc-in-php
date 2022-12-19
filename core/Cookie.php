<?php

namespace myApp\core;

class Cookie
{
    public function has($key): bool
    {
        return array_key_exists($key,$_COOKIE);
    }

    public function get($key){
        return array_get($_COOKIE,$key);
    }

    public function all():array{
        return $_COOKIE;
    }

    public function set($key,$value,$hours):void{
        setcookie($key,$value,time() + $hours * 3600 , '','',false,true);
    }

    public function remove($key):string{
        setcookie($key,null,-1);
        unset($_COOKIE[$key]);
    }

    public function destroy(){
        foreach (array_keys($this->all()) as $key){
            $this->remove($key);
        }
        unset($_COOKIE);
    }
}