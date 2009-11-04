<?php

class SystemConfig
{
    private static $instance = null;
    private $params = array();

    private function __construct()
    {
        $data = file_get_contents(APPLICATION_PATH . '/conf/main.inc');
        $this->params = eval($data);
    }
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public function get($name)
    {
        $parts = explode('.', $name);
        $target = $this->params;
        $result = null;        

        foreach ($parts as $i => $part)
        {
            if (!isset($target[$part]))
            {
                return null;
            }
            $target = $target[$part];
        }
        return $target;
    }
}
