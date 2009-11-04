<?php

include_once('./lib/http/request.php');

class HttpPage
{
	public $content = null;
	
	public function __construct($url)
	{
		$req = new HttpRequest($url, 'GET');
        $resp = $req->getResponse();
        
        $this->content = $resp->getBody();
	}
}