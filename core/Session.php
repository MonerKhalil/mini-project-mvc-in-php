<?php

namespace myApp\core;

use myApp\core\Router;

class Session
{
    /**
     * start session in app
     * @return void
     */
    public function start():void{
        ini_set("session.use_only_cookies",1);
        if(!session_id()){
            session_start();
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key){
        return array_get($_SESSION,$key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return array_key_exists($key,$_SESSION);
    }

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value):void{
        $_SESSION[$key] = $value;
    }

    /**
     * @return array
     */
    public function all():array{
        return $_SESSION;
    }

    /**
     * @param string $key
     * @return string
     */
    public function remove(string $key):string{
        if($this->has($key)){
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function pull(string $key): mixed
    {
        $temp = $this->get($key);
        return $this->remove($key) ? $temp:null;
    }

    public function clear():void{
        session_destroy();
        unset($_SESSION);
    }
}