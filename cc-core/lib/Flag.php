<?php

class Flag {
	
	private $db;
	public $flag_id;
	
	
	
	// Construct Method
	public function __construct ($flag_id, $db) {
		$this->db = $db;
		$this->flag_id = $flag_id;
	}
	
	
	
	// Create flag Method
	static function Create ($data, $db) {	
		$fields = "flag_date,";
		$values = "NOW(),";
		foreach ($data as $key => $value) {
			$value = $db->Escape ($value);
			$fields .= " $key,";
			$values .= " '$value',";
		}
		$fields = substr ($fields,0,-1);
		$values = substr ($values,0,-1);
		$query = "INSERT INTO flagging ($fields) VALUES ($values)";
		$db->Query ($query);
        return $db->id();
	}
	
	
	
	// Record Exist Method
	static function Exist ($data, $db) {		
		$query = "SELECT flag_id FROM flagging WHERE";
		foreach ($data as $key => $value) {
			$value = $db->Escape ($value);
			$query .= " $key = '$value' AND";
		}
		$query = substr ($query,0,-4);
		$result = $db->Query ($query);
		if ($db->Count ($result) == 1) {
			$row = $db->FetchRow ($result);
			return $row[0];
		} else {
			return FALSE;
		}
	}
	
	
	
}

?>