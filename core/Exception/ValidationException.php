<?php

namespace myApp\core\exception;

use Exception;

class ValidationException extends Exception
{
    protected $code = 422;
}