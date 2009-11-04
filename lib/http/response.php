<?php

class HttpResponse
{
    private $raw = null;
    private $body = null;
    private $headers = array();
    
    public function __construct($raw)
    {
        $hunks = preg_split("(\r?\n\r?\n)", trim($raw), 2);
        if (!is_array($hunks) or count($hunks) < 2)
        {
            throw new Exception('ne mogu otdelit zagolovok');
            // Exeption
        }
        $this->headers = preg_split("(\r?\n)", array_shift($hunks));
        $this->body = $this->processBody(array_shift($hunks));
    }
    
    function isGzipped()
    {
        return in_array('Content-Encoding: gzip', $this->headers);
    }
    
    function isChunked()
    {
        return in_array('Transfer-Encoding: chunked', $this->headers);
    }
    
    function processBody($str) 
    {
        if ($this->isChunked()) 
        {
            $str = $this->unchunkBody($str);            
        } 
        
        if ($this->isGzipped()) 
        { 
            $str = $this->gzInflateBody($str); 
        }
        return trim($str);
    }
    
    function unchunkBody($str)
    {
        $eol = "\r\n";
        $tmp = $str; 
        $add = strlen ($eol); 
        $str = '';
        do 
        { 
            $tmp = ltrim($tmp); 
            $pos = strpos($tmp, $eol); 
            $len = hexdec(substr($tmp, 0, $pos)); 
            $str .= substr($tmp, ($pos + $add), $len); 
            
            $tmp = substr($tmp, ($len + $pos + $add)); 
            $check = trim($tmp); 
        } 
        while (!empty($check)); 
        return $str;
    }
    
    function gzInflateBody($gzData)
    {
        if (substr($gzData, 0, 3) == "\x1f\x8b\x08")
        {
            $i = 10;
            $flg = ord(substr($gzData, 3, 1));
            if ($flg > 0)
            {
                if ($flg & 4)
                {
                    list($xlen) = unpack('v', substr($gzData, $i, 2));
                    $i= $i + 2 + $xlen;
                }
                if ($flg&8) $i = strpos($gzData, "\0", $i) + 1;
                if ($flg&16) $i = strpos($gzData, "\0", $i) + 1;
                if ($flg&2) $i = $i + 2;
            }
            return gzinflate(substr($gzData, $i, -8));
        }
        else return false;
    }
    
    public function __toString()
    {
        return $this->getBody();
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function getBody()
    {
        return $this->body;
    }
}