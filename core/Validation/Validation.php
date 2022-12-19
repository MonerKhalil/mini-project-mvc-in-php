<?php

namespace myApp\core\validation;



use Exception;
use myApp\core\exception\ValidationException;

class Validation extends Rule
{
    private function addNewMessages($newmessage){
        $curmessages = $this->getErrors();
        foreach ($curmessages as $key => $value){
            if (array_key_exists($key,$newmessage)){
                if (is_array($newmessage[$key])) {
                    foreach ($newmessage[$key] as $Nkey => $Nval) {
                        if (array_key_exists($Nkey, $value)) {
                            $curmessages[$key][$Nkey] = $Nval;
                        }
                    }
                }
            }
        }
        $this->setErrors($curmessages);
    }

    /**
     * Are there errors in entering the information?
     * @return bool
     */
    public function isFails(): bool
    {
        if (!empty($this->getErrors())){
            return true;
        }
        return false;
    }

    /**
     * ( check values ) is false => add message error
     * @param array $data : [key=>value]
     * @param array $rules : [key=>value]
     * @param array $messages : [key=>value]
     * @throws Exception
     */
    public function make(array $data, array $rules, array $messages = []): static
    {
        $this->setErrors([]);
        foreach ($rules as $key => $rule){
            $this->CheckValue($key,$data,$rule);
        }
        if (!empty($messages)){
            $this->addNewMessages($messages);
        }
        return $this;
    }

    private function CheckValue($key,$values,$rules){
        foreach ($rules as $rule){
            if (is_string($rule)){
                $Rule_Cur = explode(":",$rule);
                if (in_array($Rule_Cur[0],$this->Rules())){
                    $temp = null;
                    if (isset($Rule_Cur[1])){
                        $temp = $Rule_Cur[1];
                    }
                    $this->addRule($key,$values,$Rule_Cur[0],$temp);
                }else{
                    throw new ValidationException("rule : ".$Rule_Cur[0]."  is not exits in rules app");
                }
            }else{
                throw new ValidationException("the Value $key in array Rule Validation is not string");
            }
        }
    }

    private function addRule($key,array $values,$rule,$param){
        $value = $values[$key] ?? null;
        switch ($rule){
            case "unique":
                if (is_null($param)){
                    $param = " , ";
                }
                list($table,$col) = explode(",",$param);
                $this->unique("unique",$key,$value,$table,$col);
                break;
            case "exists":
                if (is_null($param)){
                    $param = " , ";
                }
                list($table,$col) = explode(",",$param);
                $this->exists("exists",$key,$value,$table,$col);
                break;
            case "in":
                if (is_null($param)){
                    $param = "";
                }
                $values = explode(",",$param);
                $values = is_array($values) ? $values : [];
                $this->in("in",$key,$value,$values);
                break;
            case "regex":
                if (is_null($param)){
                    $param = "";
                }
                $this->regex("regex",$key,$value,$param);
                break;
            case "length":
                if (is_null($param)){
                    $param = "";
                }
                $dataVal = explode(",",$param);
                $dataVal = is_array($dataVal) ? $dataVal : [];
                if (empty($dataVal)){
                    throw new ValidationException("Syntax Error in rule length => 'length:int || length:int,int '.");
                }
                if (isset($dataVal[1]) && is_numeric($dataVal[1])){
                    if (isset($dataVal[0]) && is_numeric($dataVal[0]))
                        $this->length_match("length",$key,$value,(int)$dataVal[0]);
                    else
                        throw new ValidationException("Syntax Error in rule length => 'length:int || length:int,int '.");
                }
                else if (isset($dataVal[0]) && is_numeric($dataVal[0])){
                    $this->between_length_match("length",$key,$value,(int)$dataVal[0],(int)$dataVal[1]);
                }else{
                    throw new ValidationException("Syntax Error in rule length => 'length:int || length:int,int '.");
                }
                break;
            case "min":
                if (!is_numeric($param)){
                    throw new ValidationException("Syntax Error in rule min => min:int.");
                }
                $this->min("min",$key,$value,$param);
                break;
            case "max":
                if (!is_numeric($param)){
                    throw new ValidationException("Syntax Error in rule max => max:int.");
                }
                $this->max("max",$key,$value,$param);
                break;
            case "match_req":
                if (is_null($param)){
                    throw new ValidationException("Syntax Error in rule match_req input request => match_req:input_in_request.");
                }
                $value2 = $values[$param] ?? null;
                $this->matchInput("match_req",$key,$value,$param,$value2);
                break;
            case "match":
                if (is_null($param)){
                    throw new ValidationException("Syntax Error in rule match => match:value.");
                }
                $value2 = $values[$param] ?? null;
                $this->match("match",$key,$value,$value2);
                break;
            case "required":
                $value = $values[$key] ?? null;
                $this->required("required",$key,$value);
                break;
            case "numeric":
                $this->int("numeric",$key,$value);
                break;
            case "float":
                $this->float("float",$key,$value);
                break;
            case "string":
                $this->string("string",$key,$value);
                break;
            case "email":
                Rule::email("email",$key,$value);
                break;
            case "array":
                $this->array("array",$key,$value);
                break;
            case "file":
                $this->isFile("file",$key,$value);
                break;
            case "image":
                if (!is_null($param)){
                    $type = explode(",",$param);
                    $this->isImage("image",$key,$type);
                }
                else{
                    $this->isImage("image",$key,$value);
                }
                break;
            case "size":
                if (!is_numeric($param)){
                    throw new ValidationException("Syntax Error in rule size => size:int.");
                }
                $this->sizeFile("size",$key,$param);
                break;
        }
    }

    private function Rules():array{
        return [
            "unique","exists","match_req","match","max","min","length",
            "required","numeric","float","string","regex","in",
            "email","array","nullable","file","image","size"
        ];
    }
}