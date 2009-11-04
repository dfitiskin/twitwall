<?php

class DatabaseMysqlHelper
{
	private $conn = null;
	
	public function __construct(DatabaseMysqlConnection $conn)
	{
		$this->conn = $conn;
	}
	
	public function escape($string)
	{
	    return mysql_escape_string($string);
	}
	
	public function select($from, $fields = '*', $where = null, $ext = null)
	{
		$sql[] = sprintf('SELECT %s FROM %s', $fields, $from);
		
		if ($where)
		{
			$sql[] = sprintf('WHERE %s', $where);
		}
		
		if ($ext)
		{
			$sql[] = $ext;
		}
		
		return $this->conn->query(implode(' ', $sql));
	}
	
	public function selectOne($from, $fields = '*', $where = null, $ext = null)
	{
		return $this->select($from, $fields, $where, $ext)->fetch();
	}
	
	public function selectAll($from, $fields = '*', $where = null, $ext = null)
	{
		return $this->select($from, $fields, $where, $ext)->fetchAll();
	}
	
	public function update($table, $fields, $where = null)
	{
		$sql[] = sprintf('UPDATE `%s` SET %s', $table, $fields);
		
		if ($where)
		{
			$sql[] = sprintf('WHERE %s', $where);
		}
		return $this->conn->query(implode(' ', $sql));
	}
	
	public function insert($table, $values, $fields = null)
	{
		if ($fields)
		{
			$fields = sprintf('(%s)', $fields);
		}

		$sql[] = sprintf('INSERT INTO `%s`%s VALUES(%s)', $table, $fields, $values);	
		$sql = implode(' ', $sql);
		return $this->conn->query($sql);
	}
	
	public function getLastInsertId()
	{
	    return $this->conn->getLastInsertId();
	}
	
	public function replace($table, $values, $fields = null)
	{
		if ($fields)
		{
			$fields = sprintf('(%s)', $fields);
		}

		$sql[] = sprintf('REPLACE INTO `%s`%s VALUES(%s)', $table, $fields, $values);		
		return $this->conn->query(implode(' ', $sql));
	}
	
	public function delete($table, $where = null)
	{
		$sql[] = sprintf('DELETE FROM `%s`', $table);
		
		if ($where)
		{
			$sql[] = sprintf('WHERE %s', $where);
		}
		return $this->conn->query(implode(' ', $sql));
	}
	
	public function updateItem($table, $item, $where = null)
	{
		$fields = array();
		foreach($item as $key => $value)
		{
			//TODO: сделать проверку на SQL функции в $value
			$fields[] = sprintf('`%s`="%s"', $key, mysql_escape_string($value));
		}
		return $this->update(
			$table,
			implode(', ', $fields),
			$where
		);
	}
	
	public function insertItem($table, $item)
	{
		$fields = array();
		$values = array();
		foreach($item as $key => $value)
		{
			//TODO: сделать проверку на SQL функции в $value
			$fields[] = sprintf('`%s`', $key);
			$values[] = sprintf('"%s"', mysql_escape_string($value));
		}
		return $this->insert(
			$table,
			implode(', ', $values),
			implode(', ', $fields)
		);
	}
	
	public function replaceItem($table, $item)
	{
		$fields = array();
		$values = array();
		foreach($item as $key => $value)
		{
			//TODO: сделать проверку на SQL функции в $value
			$fields[] = sprintf('`%s`', $key);
			$values[] = sprintf('"%s"', $value);
		}
		return $this->replace(
			$table,
			implode(', ', $values),
			implode(', ', $fields)
		);
	}
	
	public function insertItems($table, $items)
	{
		//TODO: можно сделать вставку одним запросом
		foreach ($items as $i => $item)
		{
			$this->insertItem($table, $item);
		}
	}
	
	public function createDictionary($table, $key, $value = 'id', $where = null, $ext = null)
	{
	    $res = $this->select(
	        $table,
	        sprintf('%s, %s', $key, $value),
	        $where,
	        $ext
        );
        
        $result = array();
        while ($item = $res->fetch())
        {
            $result[$item[$key]] = $item[$value];
        }
        return $result;
	}
}
