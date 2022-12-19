<?php
namespace myApp\core\exception;

use Exception;

class NotFoundException extends Exception
{
    protected $code = 404;
}