<?php

class DatabaseMysqlResult
{
	private $res = null;
	
	public function __construct($res)
	{
		$this->res = $res;
	}
	
	public function fetch()
	{
		return mysql_fetch_assoc($this->res);
	}
	
	public function fetchAll()
	{
		$result = array();
		while ($rec = $this->fetch())
		{
			$result[] = $rec;
		}
		return $result;
	}
}