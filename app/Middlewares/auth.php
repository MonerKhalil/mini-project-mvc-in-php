<?php

namespace myApp\app\middlewares;

class auth extends Middleware
{

    public function Check($next, ...$args)
    {
        #code....
        return $next;
    }
}