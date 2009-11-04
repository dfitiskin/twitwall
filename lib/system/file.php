<?php

class SystemFile
{
	private $fp = null;
	private $name = null;
	
	public function __construct($name = null)
	{
		if (!$name)
		{
			$name = tempnam(
				'./',
				'links-csv-' . date('Y-m-d H:i:s-')
			);
		}
		$this->name = $name;
		$this->fp = fopen($this->name, 'w+');
	}
	
	public function setContent($data)
	{
		fwrite($this->fp, $data);
		fseek($this->fp, 0);
	}
	
	public function close()
	{
		fclose($this->fp);
	}
}