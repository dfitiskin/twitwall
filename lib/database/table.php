<?php

class DatabaseTable
{
    private $conn = null;
    private $name = null;
    private $prKey = 'id';
    
    public function __construct($name, $conn = null, $prKey = 'id')
    {
        $this->conn = $conn ? $conn->getHelper() : DatabaseConnection::getInstance()->getHelper();
        $this->name = $name;
        $this->prKey = $prKey;
    }
    
    public function load($key)
    {
        return $this->conn->selectOne(
            $this->name,
            '*',
            sprintf(
                '`%s`="%d"',
                $this->conn->escape($this->prKey),
                $this->conn->escape($key)
            )
        );
    }
    
    public function insert($rec)
    {
        $this->conn->insertItem(
            $this->name,
            $rec
        );
        return $this->conn->getLastInsertId();
    }
    
    public function update($key, $rec)
    {
        return $this->conn->updateItem(
            $this->name,
            $rec,
            sprintf(
                '`%s`="%d"',
                $this->conn->escape($this->prKey),
                $this->conn->escape($key)
            )
        );
    }
    
    public function delete($key)
    {
        return $this->conn->delete(
            $this->name,
            sprintf(
                '`%s`="%d"',
                $this->conn->escape($this->prKey),
                $this->conn->escape($key)
            )
        );
    }
}