<?php

include_once('result.php');
include_once('helper.php');
include_once('database/exception.php');

class DatabaseMysqlConnection
{
	private $conn = null;
	private $res = null;
	
	public function __construct($host, $user, $pw, $db)
	{
		$level = error_reporting(0);
		$this->conn = mysql_connect($host, $user, $pw);
		error_reporting($level);
		
	    if (!$this->conn)
		{
			throw new DatabaseException('Unable to connect to DB: ' . mysql_error());
		}
		
		$level = error_reporting(0);
		$res = mysql_select_db($db, $this->conn);
		error_reporting($level);
		
		if (!$res)
		{
			throw new DatabaseException('Unable to select mydbname: ' . mysql_error());
		}
		$this->query('SET NAMES CP1251');
	}
	
	public function query($sql)
	{
		$this->res = mysql_query($sql, $this->conn);
		
		if (!$this->res)
		{
			throw new DatabaseException(
				sprintf(
					'Could not successfully run query (%s) from DB: %s',
					$sql,
					mysql_error()
				)
			);
		}
		return new DatabaseMysqlResult($this->res);
	}
	
	public function getLastInsertId()
	{
	    return mysql_insert_id($this->conn);
	}
	
	public function getHelper()
	{
		return new DatabaseMysqlHelper($this);
	}
}