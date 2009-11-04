<?php

include_once('./lib/system/file.php');

class SystemCsvFileDecorator
{
	private $file = null;
	private $partLength = 1024;
	private $delimiter = ';';
	private $enclosure = '"';
	
	public function __construct(SystemFile $file, $delimiter = ';', $enclosure = '"', $partLength = 1024)
	{
		$this->file = $file;
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->partLength = $partLength;
	}
	
	
	public function nextRow()
	{
		$row = fgetcsv(
			$this->file->fp, 
			$this->partLength,
			$this->delimiter, 
			$this->enclosure
		);
	}
	
}