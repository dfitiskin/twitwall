<?php

class TwitterUser
{
    private $id = null;
    private $screenname = null;
    
    private $params = array();
    
    public function __construct($id, $screenname)
    {
        $this->screenname = $screenname;
        $this->id = $id;
    }
    
    public function getScreenName()
    {
        return $this->screenname;
    }
    
    private function set($param, $value)
    {
        $this->params[strtolower($param)] = $value;
    }
    
    private function get($param)
    {
        $result = null;
        $param = strtolower($param);
        if (isset($this->params[$param]))
        {
            $result = $this->params[$param];
        }
        return $result;
    }
    
    public function __call($name, $args)
    {
        if (0 == count($args) && preg_match('(get([a-z]+))i', $name, $parts))
        {
            return $this->get($parts[1]);
        }
        elseif (1 == count($args) && preg_match('(set([a-z]+))i', $name, $parts))
        {
            return $this->set($parts[1], $args[0]);
        }
    }
}