<?php

class SystemDate
{
    private $timeStamp = 0;
    
    public function __construct($date)
    {
        $this->timeStamp = strtotime($date);
    }
    
    public function toSql()
    {
        return date('Y-m-d H:i:s', $this->timeStamp);
    }
    
    public function __toString()
    {
        return date('d.m.Y H:i', $this->timeStamp);
    }
}