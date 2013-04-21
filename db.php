<?php

/*
 * @author _Tobias
 * @dependencies php-sqlite3 / functions.php
 */

class Database {
	private $archFile = 'architecture.json';
	private $arch;
	private $dbFile;
	public $db;

	public function __construct($dbFile, $archFile) {

		require_once('functions.php');

		if(isset($archFile))
			$this->archFile = $archFile;

		if(isset($dbFile))
			$this->dbFile = $dbFile;

		if(!is_file($this->archFile))
			die('Database: Architecture file doesn\'t exist.');

		$this->arch = jd(file_get_contents($this->archFile));
		if(!$this->arch)
			die('Database: Invalid Architecture file.');

		$this->db = new SQLite3($this->dbFile);
		if(!$this->db)
			die('Database: Can\'t open database.');
	}

	public function __destruct() {
		if($this->db)
			$this->close();
	}

	public function insertArray($arr, $into) {
		$values = array();
		foreach($arr as $field => $value) {
			if(!isset($this->arch[$into][$field]))
				die('Database: Unknown field specified.');
			if(strpos($this->arch[$into][$field], 'char') !== false || strpos($this->arch[$into][$field], 'text') !== false)
				$values[] = "'".str_replace("'", "\'", $value)."'";
			elseif($value == null)
				$values[] = 'NULL';
			elseif($value === true || $value === false)
				$values[] = $value ? '1' : '0';
			else
				$values[] = $value;
		}
		return $this->db->query(sprintf('INSERT INTO '.$into.' (%s) VALUES ("%s")', implode(', ', array_keys($arr)), implode('","', array_values($arr))));
	}

	public function createArch($name) {
		if(!isset($this->arch[$name]))
			die('Database: Selected Architecture doesn\'t exist.');

		$query = 'CREATE TABLE '.$name.' (';

		foreach($this->arch[$name] as $field => $type)
			$query .= $field.' '.$type.', ';

		return @$this->db->exec(substr($query, 0, strlen($query)-2).')');
	}

	public function query($q) {
		return $this->db->query($q);
	}

	public function exec($q) {
		return $this->db->exec($q);
	}

	public function close() {
		return $this->db->close();
	}

	public function getError() {
		return $this->db->lastErrorMsg();
	}
}