<?php

namespace myApp\core\http;

class UploadedFile
{
    private array $file = [];

    private string $filenamefull;

    private string $filename;

    private $extension;

    private string $mimeType;
    /*
     *  temp file main
     */
    private string $tempfile;

    private int $size;

    private $errors;

    public function __construct(string $input)
    {
        $this->getFileInfo($input);
    }

    private function getFileInfo(string $file){
        if (empty($_FILES[$file])){
            return false;
        }
        $temp = $_FILES[$file];
        $this->errors = $temp['error'];
        if ($this->errors != UPLOAD_ERR_OK){
            return false;
        }
        $this->file = $temp;
        $this->filenamefull = $this->file['name'];
        $infofile = pathinfo($this->filenamefull);
        $this->filename = $infofile['basename'];
        $this->extension = strtolower($infofile['extension']);
        $this->mimeType = $this->file['type'];
        $this->tempfile = $this->file['tmp_name'];
        $this->size = $this->file['size'];
        return true;
    }

    public function exists(){
        return !empty($this->file);
    }

    public function getfileName(){
        return $this->filenamefull;
    }

    public function getNameOnly(){
        return $this->filename;
    }

    public function getExtension(){
        return $this->extension;
    }

    public function getMimeType(){
        return $this->mimeType;
    }

    public function getSize(){
        return $this->size;
    }

    public function getTempFile(){
        return $this->tempfile;
    }

    public function isImage(){
        return str_starts_with($this->mimeType, "image/") &&
            in_array($this->extension,$this->getTypesImage());
    }

    public function saveFile(string $pathDir,string $newName = null){
        $filename = $newName ?? sha1(mt_rand()).'_'.sha1(mt_rand());
        $filename .= '.'.$this->getExtension();
        if (!is_dir($pathDir)){
            mkdir($pathDir,0777,true);
        }
        $pathAll = rtrim($pathDir,'/').'/'. $filename;
        move_uploaded_file($this->tempfile,$pathAll);
    }

    public function getTypesImage(){
        return ['bmp','ico','cur','tif','tiff','jpg','jpeg','jfif','pjpeg','pjp','png','apng','avif','gif','svg','webp'];
    }
}