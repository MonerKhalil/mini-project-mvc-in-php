<?php

namespace myApp\core\validation;

use myApp\core\Application;
use myApp\core\exception\ValidationException;

abstract class Rule
{
    private array $nullableVar = [];
    private array $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }
    protected function setErrors($errors){
        $this->errors = $errors;
    }

    protected function nullable(string $inputName){
        $this->nullableVar[$inputName] = true;
    }

    /**
     * unique:table,column
     * @param $Rule
     * @param string $inputName
     * @param mixed $value
     * @param string $table
     * @param string $col
     * @param string|null $msg
     * @return bool
     */
    protected function unique($Rule, string $inputName, mixed $value, string $table, string $col, string $msg = null): bool
    {
        $item = Application::getApp()->DB->table($table)->where($col,$value)->first();
        if (!is_null($item)){
            $this->addErrorMessage($Rule,$inputName,"already exists in ".$table,$msg);
            return false;
        }
        return true;
    }

    /**
     * exists:table,column
     * @param $Rule
     * @param string $inputName
     * @param mixed $value
     * @param string $table
     * @param string $col
     * @param string|null $msg
     * @return bool
     */
    protected function exists($Rule, string $inputName, mixed $value, string $table, string $col, string $msg = null): bool
    {
        $item = Application::getApp()->DB->table($table)->where($col,$value)->first();
        if (is_null($item)){
            $this->addErrorMessage($Rule,$inputName,"not exists in ".$table,$msg);
            return false;
        }
        return true;
    }

    /**
     * file
     * @param $Rule
     * @param string $inputName
     * @param string|null $msg
     * @return bool
     */
    protected function isFile($Rule, string $inputName, string $msg = null): bool
    {
        $file = Application::getApp()->request->file($inputName);
        if (!$file->exists()){
            $this->addErrorMessage($Rule,$inputName,"not file",$msg);
            return false;
        }
        return true;
    }

    /**
     * image or image:jpg,png, .....
     * @param $Rule
     * @param string $inputName
     * @param array|null $type
     * @param string|null $msg
     * @return bool
     * @throws ValidationException
     */
    protected function isImage($Rule, string $inputName, array $type = null, string $msg = null): bool
    {
        try {
            $file = Application::getApp()->request->file($inputName);
            if ($this->isFile($Rule,$inputName)){
                if (!$file->isImage()){
                    $this->addErrorMessage($Rule,$inputName,"not Image",$msg);
                    return false;
                }
                if (!is_null($type) and is_array($type)){
                    foreach ($type as $value){
                        if (!in_array($value,$file->getTypesImage())){
                            Throw new ("the type image ".$value."is not exists in Extension image in app");
                        }
                    }
                    if (!in_array($file->getExtension(),$type)){
                        $this->addErrorMessage($Rule,$inputName,"current image format is rejected",$msg);
                        return false;
                    }
                }
                return true;
            }
            return false;
        }catch (\Exception $exception){
            throw new ValidationException($exception->getMessage());
        }
    }

    protected function sizeFile($Rule,string $inputName,$size,$msg = null): bool
    {
        $file = Application::getApp()->request->file($inputName);
        if ($this->isFile($Rule,$inputName)){
            if (round($file->getSize() / 1024 / 1024,1) > $size){
                $this->addErrorMessage($Rule,$inputName,"The current file size is greater than ".$size,$msg);
                return false;
            }
            return true;
        }
        return false;
    }

    protected function regex($Rule,string $inputName,mixed $value,string $reg,string $msg = null): bool
    {
        if (!preg_match($reg,$value)){
            $this->addErrorMessage($Rule,$inputName,"not match with Regular Expression pattern",$msg);
            return false;
        }
        return true;
    }

    protected function in($Rule,string $inputName,mixed $value,array $values,string $msg = null): bool
    {
        if (!in_array($value,$values)){
            $this->addErrorMessage($Rule,$inputName,"value not among the required values",$msg);
            return false;
        }
        return true;
    }

    /**
     * length:3,10
     * @param $Rule
     * @param string $inputName
     * @param mixed $value
     * @param int $len
     * @param string|null $msg
     * @return bool
     */
    protected function length_match($Rule, string $inputName, mixed $value, int $len, string $msg = null): bool
    {
        $reg = '/^.{'.$len.'}$/';
        if (!preg_match($reg,$value)){
            $this->addErrorMessage($Rule,$inputName,"must be ".$len." characters long",$msg);
            return false;
        }
        return true;
    }

    /**
     * length:10
     * @param $Rule
     * @param string $inputName
     * @param mixed $value
     * @param int $len1
     * @param int $len2
     * @param string|null $msg
     * @return bool
     */
    protected function between_length_match($Rule, string $inputName, mixed $value, int $len1, int $len2, string $msg = null): bool
    {
        $reg = '/^.{'.$len1.','.$len2.'}$/';
        if (!preg_match($reg,$value)){
            $this->addErrorMessage($Rule,$inputName,"must be between ".$len1." and ".$len2." characters",$msg);
            return false;
        }
        return true;
    }

    protected function min($Rule,string $inputName,mixed $value,int $length,string $msg = null): bool
    {
        if (self::int($Rule,$inputName,$value,$msg)){
            if($value >= $length){
                $this->addErrorMessage($Rule,$inputName,"not min ".$length,$msg);
                return false;
            }
            return true;
        }
        return false;
    }

    protected function max($Rule,string $inputName,mixed $value,int $length,string $msg = null): bool
    {
        if (self::int($Rule,$inputName,$value,$msg)){
            if($value <= $length){
                $this->addErrorMessage($Rule,$inputName,"not max ".$length,$msg);
                return false;
            }
            return true;
        }
        return false;
    }

    protected function matchInput($Rule,string $inputName1,mixed $value1,string $inputName2,mixed $value2,string $msg = null): bool
    {
        if ($value2!==$value1){
            $this->addErrorMessage($Rule,$inputName2,"should match ".$inputName1,$msg);
            return false;
        }
        return true;
    }
    protected function match($Rule,string $inputName1,mixed $value1,mixed $value2,string $msg = null): bool
    {
        if ($value2!==$value1){
            $this->addErrorMessage($Rule,$inputName1,"should match ".(string)$value2,$msg);
            return false;
        }
        return true;
    }

    protected function required($Rule,string $inputName,mixed $value,string $msg = null): bool
    {
        if (is_null($value)){
            $this->addErrorMessage($Rule,$inputName,"required",$msg);
            return false;
        }
        return true;
    }

    protected function int($Rule,string $inputName,mixed $value,string $msg = null): bool
    {
        if (!is_numeric($value)){
            $this->addErrorMessage($Rule,$inputName,"not numeric",$msg);
            return false;
        }
        return true;
    }

    protected function float($Rule,string $inputName,mixed $value,string $msg = null): bool
    {
        $tempvalue = is_numeric($value) ? (float) $value : $value;
        if (!is_float($tempvalue)){
            $this->addErrorMessage($Rule,$inputName,"not float",$msg);
            return false;
        }
        return true;
    }

    protected function string($Rule,string $inputName,mixed $value,string $msg = null): bool
    {
        if (!is_string($value)){
            $this->addErrorMessage($Rule,$inputName,"not string",$msg);
            return false;
        }
        return true;
    }

    protected function array($Rule,string $inputName,mixed $value,string $msg = null): bool
    {
        if (!is_array($value)){
            $this->addErrorMessage($Rule,$inputName,"not array",$msg);
            return false;
        }
        return true;
    }

    protected function email($Rule,string $inputName,mixed $value,string $msg = null): bool
    {
        if (!filter_var($value,FILTER_VALIDATE_EMAIL)){
            $this->addErrorMessage($Rule,$inputName,"not email",$msg);
            return false;
        }
        return true;
    }

    private function addErrorMessage($Rule,string $inputName,string $ruleMsg,string $msg = null)
    {
        $msg = $cusMsg ?? "the ".$inputName. " input is ".$ruleMsg.".";
        if (array_key_exists($inputName,$this->errors)){
            if(is_array($this->errors[$inputName])){
                if(!in_array($msg,$this->errors[$inputName])){
                    $this->errors[$inputName][$Rule] = $msg;
                }
            }else{
                $this->errors[$inputName][$Rule] = $msg;
            }
        }else{
            $this->errors[$inputName][$Rule] = $msg;
        }
    }
}