<?php

namespace myApp\core\http\response;

use myApp\core\Application;
use myApp\core\exception\NotFoundException;

class View implements ResponseFactory
{
    private $fileView;
    private array $data = [];
    public function __construct($fileView,array $data)
    {
        $this->fileView = Application::getApp()->fileSystem->getDirRoot()."/app/views/$fileView.php";
        $this->data = $data;
    }

    public function renderContent(): bool|string
    {
        if (!Application::getApp()->fileSystem->isExists($this->fileView)){
            Throw new NotFoundException("the View $this->fileView is not Found !!");
        }
        ob_start();
        extract($this->data);
        require_once $this->fileView;
        return ob_get_clean();
    }
}