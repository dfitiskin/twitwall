<?php

class SystemUrl
{
    private $url = null;
    
    public function __construct($url)
    {
        $this->url = $url;
    }
    
    public function isIndex()
    {
        $level = error_reporting(0);
        $path = parse_url($this->url, PHP_URL_PATH);
        $query = parse_url($this->url, PHP_URL_QUERY);
        $level = error_reporting($level);
        
        $result = in_array($path, array('', '/', '/index.html', '/index.php', '/index.shtml')) && empty($query);
        return $result;
    }
}