<?php

namespace myApp\app\models;

use myApp\core\database\DataBase;

class Model extends DataBase
{
    protected ?string $table = "";
    protected $primaryKey = "id";
}