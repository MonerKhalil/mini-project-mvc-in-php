<?php

namespace myApp\core\database;

class WhereAttr
{
    private $column;
    private $value;
    private $operation;
    private $condition;

    public function __construct($column,$value,$operation,$condition = "AND")
    {
        $this->column =$column;
        $this->value = $value;
        $this->condition = $condition;
        $this->operation = $operation;
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }
}