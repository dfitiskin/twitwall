<?php

include_once('./lib/http/response.php');

class HttpRequest
{
    private $blockSize = 8192;
    private $host = null;
    private $port = 80;
    private $url = null;
    private $query = null;
    private $method = 'GET';
    private $params = array();
    private $headers = array();
    
    public function __construct($url, $method = 'GET', $params = array(), $headers = array())
    {
        $this->host = parse_url($url, PHP_URL_HOST);        
        $this->url = parse_url($url, PHP_URL_PATH);
        $this->query = parse_url($url, PHP_URL_QUERY);
        $this->method = $method;
        $this->headers = $headers;
        $this->body = http_build_query($params);
        $this->headers[] = 'Content-Length: '.strlen($this->body);
        
        if ($port = parse_url($url, PHP_URL_PORT))
        {
            $this->port = $port;
        }
    }
    
    private function getRequestHeaders()
    {
        $result = array();
        $result[] = $this->method.' '.$this->url.' HTTP/1.1';
        $result[] = 'Host: '.$this->host;
        $result[] = implode("\r\n", $this->headers);
        $result[] = 'Accept-Encoding: gzip,deflate';
        $result[] = 'Connection: close';
        $result[] = '';
        $result[] = $this->body;
        return implode("\r\n", $result);
    }
    
    public function getResponse()
    {
        $socket = fsockopen($this->host, $this->port);
		fwrite($socket, $this->getRequestHeaders());
		$response = '';
		while ($socket && !feof($socket))
		{
			$response .= fread($socket, $this->blockSize);
		}
		fclose($socket);
		return new HttpResponse($response);
    }
}