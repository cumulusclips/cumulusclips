<?php

class Picture {

    private $db;
    public $pic_id;
    public $found;
    public $user_id;
    public $file;
    public $extension;
    public $date_created;



    // Construct Method
    public function __construct($pic_id, $db) {
        $this->db = $db;
        $this->pic_id = $pic_id;
        $this->found = $this->Check();
        if ($this->found) {
            $this->Get();
        }
    }



    // Verify record is valid Method
    private function Check() {
        $id = $this->db->Escape ($this->pic_id);
        $query = "SELECT pic_id FROM pictures WHERE pic_id = '$id'";
        $result = $this->db->Query ($query);
        if ($this->db->Count ($result) == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }



    // Retrieve record Method
    private function Get() {
        $query = "SELECT * FROM pictures WHERE pic_id = $this->pic_id";
        $result = $this->db->Query ($query);
        $row = $this->db->FetchAssoc ($result);
        $this->file = $row['file'];
        $this->extension = $row['extension'];
        $this->user_id = $row['user_id'];
        $this->date_created = $row['date_created'];
    }
    
    
    
    // Check if record exist Method
    static function Exist ($data, $db) {
        
        $query = "SELECT pic_id FROM pictures WHERE";
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
    
    
    
    // Update record Method
    public function Update ($data) {

        $query = "UPDATE pictures SET";
        foreach ($data as $key => $value) {
            $value = $this->db->Escape ($value);
            $query .= " $key = '$value', ";
        }        
        $query = substr ($query,0,-2) . " WHERE pic_id = $this->pic_id";
        $this->db->Query ($query);
        $this->Get();

    }



    // Insert Record Method
    static function Create ($data, $db) {

        $fields = 'date_created,';
        $values = 'NOW(),';

        foreach ($data as $key => $value) {
            $value = $db->Escape ($value);
            $fields .= " $key,";
            $values .= " '$value',";
        }

        $fields = substr ($fields,0,-1);
        $values = substr ($values,0,-1);
        $query = "INSERT INTO pictures ($fields) VALUES ($values)";
        $db->Query ($query);
        return $db->id();

    }



    // Create a unique random string
	static function CreateUnique ($db) {
		$count = TRUE;
		while ($count) {
			$code = Random (15);
			if (!Picture::Exist (array ('file' => $code), $db)) {
				$count = NULL;
			}
		}
		return $code;
	}



}

?>
