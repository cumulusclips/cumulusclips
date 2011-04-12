<?php

class Category {

	private $db;
	public $cat_id;
	public $found;
	
	
	// Construct Method
	public function __construct ($id, $db) {
		$this->db = $db;
		$this->$cat_id = $id;
		$this->found = $this->Check();
		if ($this->found) {
			$row = $this->Get();
			$this->cat_name = $row['cat_name'];
			$this->dashed = str_replace (' ', '-', $this->cat_name);
			$this->cat_desc = $row['cat_desc'];
			$this->date_created = $row['date_created'];
		}
	}
	
	
	
	// Check category exist Method
	private function Check() {	
		$query = "SELECT cat_id FROM categories WHERE cat_id = $this->cat_id";
		$result = $this->db->Query ($query);
		if ($this->db->Count ($result) == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	
	// Retrieve category Method
	private function Get() {	
		$query = "SELECT * FROM categories WHERE cat_id = $this->cat_id";
		$result = $this->db->Query ($query);
		return $this->db->FetchAssoc ($result);
	}
	
	
	
	// Verify category exists Method
	static function Exist ($data, $db) {
	
		$where = '';
				
		foreach ($data as $key => $value) {
			$value = $db->Escape ($value);
			$where .= "$key = '$value' AND ";
		}
		
		$where = substr ($where,0,-5);
		$result = $db->Query ("SELECT cat_id FROM categories WHERE $where");
		if ($db->Count ($result) == 1) {
			$row = $db->FetchRow ($result);
			return $row[0];
		} else {
			return FALSE;
		}
		
	}
	
}

?>