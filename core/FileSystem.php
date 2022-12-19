<?php

namespace myApp\core;

use myApp\core\Router;

class FileSystem
{
    private const DS = DIRECTORY_SEPARATOR;
    private string $root;

    public function __construct($fileRoot)
    {
        $this->root = $fileRoot;
        $this->root = str_replace(["/","\\","//"],self::DS,$this->root);
    }
    public function isExists($path_file): bool
    {
        return file_exists($path_file);
    }
    public function Require($path_file){
        return require $path_file;
    }

    public function ToFullPath($path): string
    {
        return $this->root . self::DS . str_replace(["/","\\","//"],self::DS,$path);
    }
    public function getDirRoot()
    {
        return $this->root;
    }
}