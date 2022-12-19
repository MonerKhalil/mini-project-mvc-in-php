<?php

namespace myApp\core\database;

use myApp\core\Application;
use myApp\core\exception\DataBaseException;
use PDO;

class DataBase
{
    protected $connection;
    /**
     * @var string | null
     */
    protected ?string $table = null;
    /**
     * @var array
     */
    protected array $bindings = [];
    /**
     * @var array
     */
    protected array $where = [];
    /**
     * @var array
     */
    protected array $selects = [];
    /**
     * @var array
     */
    protected array $joins = [];
    /**
     * @var array
     */
    protected array $orderBy = [];

    /**
     * @var int|null
     */
    protected ?int $limit = null;
    /**
     * @var int|null
     */
    protected ?int $offset = null;
    /**
     * @var int | null
     */
    protected ?int $lastInsertId = null;
    /**
     * @var int | null
     */
    protected ?int $rows = null;

    public function __construct()
    {
//        if(!$this->isConnected()){
//            $this->Connect();
//        }
    }

    /**
     * @return bool
     */
    protected function isConnected(): bool
    {
        return $this->connection instanceof PDO;
    }
    /**
     * Connect DataBase in information in file config.php
     *@return void
     */
    protected function Connect(){
        try {
            $DataConnection = Application::getApp()->getConfig()['database'];
            $dns = "mysql:host=".$DataConnection['server'].";dbname=".$DataConnection['db_name'];
            $Temp = new PDO($dns,$DataConnection['db_username'],$DataConnection['db_password']);
            $Temp->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $Temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $Temp->exec("SET NAMES UTF8");
            $this->connection = $Temp;
        }catch (\Exception $PDOException){
            throw new DataBaseException($PDOException->getMessage());
        }
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if (!$this->isConnected() && is_null($this->connection)){
            $this->Connect();
        }
        return $this->connection;
    }

    public function beginTransaction(){
        return $this->getConnection()->beginTransaction();
    }

    public function commit(){
        return $this->getConnection()->commit();
    }

    public function rollback(){
        return $this->getConnection()->rollback();
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table): DataBase
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function from($table): DataBase
    {
        return $this->table($table);
    }

    /**
     * write sql statement and multi argument in sql
     * @param $sql_query
     * @param ...$bindings
     * @return false|\PDOStatement|void
     */
    public function query($sql_query, ...$bindings){
        try {
            if(count($bindings)===1 && is_array($bindings[0])){
                $bindings = $bindings[0];
            }
            $query = static::getConnection()->prepare($sql_query);
            foreach ($bindings as $key => $value){
                $query->bindValue( $key + 1 , _e($value));
            }
//            print_r($this->bindings);
//            dd($query);
            $query->execute();
            return $query;
        }catch (DataBaseException $PDOException){
            throw new DataBaseException($PDOException->getMessage());
        }
    }

    /**
     * @param ...$selects
     * @return $this
     */
    public function select(...$selects): DataBase
    {
        if(!empty($selects)){
            if(is_array($selects[0])){
                $selects = $selects[0];
            }
        }
        $this->selects = $selects ?? [];
        return $this;
    }

    /**
     * @param $table2
     * @param $col1
     * @param $col2
     * @param string $type
     * @return $this
     */
    public function join($table2, $col1, $col2, string $type = ""): DataBase
    {
        $sql_join = $type ." JOIN " . "`".$table2."` ON ". "`".$col1."`". " = "."`".$col2."` ";
        $this->joins[] = $sql_join;
        return $this;
    }

    /**
     * @param string|array $columns
     * @param string $sort
     * @return $this
     */
    public function orderBy($columns, string $sort = "ASC"): DataBase
    {
        if(is_array($columns)){
            $temp = "";
            foreach ($columns as $column){
                $temp .= "`".$column."`, ";
            }
            $temp = rtrim($temp,", ");
            $this->orderBy = [$temp,$sort];
        }
        else{
            $this->orderBy = [$columns,$sort];
        }
        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): DataBase
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): DataBase
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return array|false
     *
     */
    public function get(){
        $sql = "SELECT ";
        if(empty($this->selects)){
            $sql .= "*";
        }else{
            $sql .= implode(",",$this->selects);
        }
        $sql .= " FROM " ."`". $this->table ."`"." ";
        if(!empty($this->joins)){
            $sql .= implode(" ",$this->joins);
        }
        if(!empty($this->where)){
            $sql = $this->getSqlWhere($sql);
        }

        $sql .= !is_null($this->limit) ? " LIMIT ".$this->limit : "";
        $sql .= !is_null($this->offset) ? " OFFSET ".$this->offset : "";
        if(!empty($this->orderBy)){
            $sql .= " ORDER BY ".implode(" ",$this->orderBy);
        }
        $query = $this->query($sql,$this->bindings);
        $items = $query->fetchAll();
        $this->rows = $query->rowCount();
        $this->Reset();
        return empty($items) ? null : $items;
    }

    /**
     * @return mixed
     */
    public function first(){
        $sql = "SELECT ";
        if(empty($this->selects)){
            $sql .= "*";
        }else{
            $sql .= implode(",",$this->selects);
        }
        $sql .= " FROM " . "`". $this->table ."`" ." ";
        if(!empty($this->joins)){
            $sql .= implode(" ",$this->joins);
        }
        if(!empty($this->where)){
            $sql = $this->getSqlWhere($sql);
        }
        $sql .= " LIMIT 1 ";
        if(!empty($this->orderBy)){
            $sql .= " ORDER BY ".implode(" ",$this->orderBy);
        }
        $query = $this->query($sql,$this->bindings);
        $items = $query->fetch();
        $this->rows = $query->rowCount();
        $this->Reset();
        return empty($items) ? null : $items;
    }

    /**
     * @param $keyword
     * @param $data
     * @param null $table
     * @return string
     */
    private function Update_Insert($keyword, $data, $table = null): ?string
    {
        try {
            if(!is_null($table)){
                $this->table($table);
            }
            if ($keyword === "INSERT"){
                $sql = $keyword. " INTO " . "`". $this->table ."`" . " SET ";
            }else{
                $sql = $keyword ." ". "`". $this->table ."`" . " SET ";
            }
            foreach ($data as $key =>$value){
                $sql .= "`" . $key ."` = ? , ";
                $this->addBindings(((string)$value));
            }
            $sql = rtrim($sql,", ");
            if($keyword ==="UPDATE"){
                if(!empty($this->where)){
                    $sql = $this->getSqlWhere($sql);
                }
            }
            $this->query($sql,$this->bindings);
            $this->lastInsertId = static::getConnection()->lastInsertId();
            return $this->table;
        }catch (DataBaseException $PDOException){
            throw new DataBaseException($PDOException->getMessage());
        }
    }

    private function getSqlWhere(string $sql):string{
        $sql .= " WHERE ";
        $where = "";
        $Temp_delete = "";
        foreach ($this->where as $item){
            if($item->getOperation() === "IN"){
                $where .= $item->getCondition() . "`".$item->getColumn()."` "
                    . $item->getOperation() ." (";
                foreach ($item->getValue() as $value){
                    $where .= "? , ";
                    $this->addBindings($value);
                }
                $where = rtrim($where,", ");
                $where .= ") ";
            }
            else if ($item->getOperation() === "between"){
                $where .= $item->getCondition() . "`".$item->getColumn()."` "
                    . $item->getOperation() . " ";
                foreach ($item->getValue() as $value){
                    $where .= "? AND ";
                    $this->addBindings($value);
                }
                $where = rtrim($where,"AND ");
            }
            else if ($item->getOperation() === "Raw"){
                $where .= $item->getCondition() .$item->getColumn()." ";
                foreach ($item->getValue() as $value){
                    $this->addBindings($value);
                }
            }
            else{
                $where .= $item->getCondition() . "`".$item->getColumn()."` "
                    . $item->getOperation() . " ?";
                $this->addBindings($item->getValue());
            }
            if($Temp_delete===""){
                $Temp_delete = $item->getCondition();
            }
        }
        $where = ltrim($where,$Temp_delete);
        return $sql . $where;
    }

    /**
     * @param $data
     * @param string | null $table
     * @return array|bool
     */
    public function insert($data,string $table = null){
        $temp_table = $this->Update_Insert("INSERT",$data,$table);
        $this->Reset();
        $this->table($temp_table);
        return $this->where("id",$this->getLastIdInsert())->first();
    }

    /**
     * @param $data
     * @param string|null $table
     * @return true
     */
    public function update($data,string $table = null): bool
    {
        $this->Update_Insert("UPDATE",$data,$table);
        $this->Reset();
        return true;
    }
    /**
     * @param string|null $table
     * @return bool
     */
    public function delete(string $table = null): bool
    {
        if(!is_null($table)){
            $this->table($table);
        }
        $sql = "DELETE FROM " . $this->table;
        if(!empty($this->where)){
            $sql = $this->getSqlWhere($sql);
        }
        $this->query($sql,$this->bindings);
        $this->Reset();
        return true;
    }

    /**
     * @param string $column
     * @param null $operation
     * @param null $value
     * @param string $condition
     * @return $this
     */
    public function where(string $column, $operation=null, $value=null, string $condition = "AND"):DataBase{
        if(!is_null($operation)&&!is_null($value)){
            if(!$this->CheckOperation($operation)){
                $operation = "=";
            }}
        if(is_null($value)){
            $value = $operation;
            $operation = "=";
        }
        $this->where[] = new WhereAttr($column,$value,$operation,$condition);
        return $this;
    }
    /**
     * @param string $RawSql
     * @param ...$bindings
     * @return $this
     */
    public function whereRaw(string $RawSql, ...$bindings): DataBase
    {
        if(is_array($bindings[0])){
            $bindings = $bindings[0];
        }
        $this->where[] = new WhereAttr($RawSql,$bindings,"Raw");
        return $this;
    }
    /**
     * @param string $column
     * @param $value1
     * @param $value2
     * @param string $condition
     * @return $this
     */
    public function whereBetween(string $column, $value1, $value2, $condition = "AND"): DataBase{
        $value = [$value1,$value2];
        return $this->where($column,"between",$value,$condition);
    }
    /**
     * @param string $column
     * @param array $values
     * @param string $condition
     * @return $this
     */
    public function whereIn(string $column, array $values, $condition = "AND"): DataBase{
        return $this->where($column,"IN",$values,$condition);
    }

    /**
     * @param string $column
     * @param null $operation
     * @param null $value
     * @return $this
     */
    public function orWhere(string $column, $operation=null, $value=null):DataBase{
        return $this->where($column,$operation,$value,"OR");
    }

    /**
     * @param string $column
     * @param $value1
     * @param $value2
     * @return $this
     */
    public function orWhereBetween(string $column, $value1, $value2): DataBase{
        return $this->whereBetween($column,$value1,$value2,"OR");
    }

    /**
     * @param string $column
     * @param array $values
     */
    public function orWhereIn(string $column, array $values){
        $this->whereIn($column,$values,"OR");
    }

    /**
     * @param string $RawSql
     * @param ...$bindings
     * @return $this
     */
    public function orWhereRaw(string $RawSql, ...$bindings): DataBase
    {
        if(is_array($bindings[0])){
            $bindings = $bindings[0];
        }
        $this->where[] = new WhereAttr($RawSql,$bindings,"Raw","OR");
        return $this;
    }

    public function getLastIdInsert(){
        return $this->lastInsertId;
    }
    private function addBindings($value){
        $this->bindings[] = $value;
    }
    /**
     * @param $operation
     * @return bool
     */
    private function CheckOperation($operation): bool
    {
        $core_operation = [
            "=","!=","<",">",">=","<=","like","IN","between"
        ];
        return in_array($operation,$core_operation);
    }

    private function Reset(){
        $this->bindings = [];
        $this->selects = [];
        $this->where = [];
        $this->joins = [];
        $this->orderBy = [];
        $this->offset = null;
        $this->limit = null;
        $this->rows = null;
    }

}