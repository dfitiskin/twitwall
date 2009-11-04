<?php

class HttpCurlRequest
{
	private $ch = null;
	
	public function __construct($url)
	{
		$this->ch = curl_init($url);
		//curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, "sschecker");
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, "sschecker");
	}
	
	public function setBasicAuth($login, $pw)
	{
		curl_setopt(
			$this->ch, 
			CURLOPT_USERPWD, 
			sprintf('%s:%s', $login, $pw)
		);
	}
	
	public function setIncludeHttpHeaders()
	{
		curl_setopt($this->ch, CURLOPT_HEADER, 1);
	}
	
	public function setUserAgent($agent)
	{
		curl_setopt($this->ch, CURLOPT_USERAGENT, $agent);		
	}
	
	public function setVerbose()
	{
		curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
	}
	
	public function setHeaders($headers)
	{
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
	}
	
	public function setPostData($data)
	{
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
	}
	
	public function getResponse()
	{
		$result = curl_exec($this->ch);
		curl_close($this->ch);
		return $result;
	}
}