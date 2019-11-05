<?php
class ModelExtensionModuleCallback extends Model {	
	public function addCallback($data) {

    	  	$query = $this->db->query("INSERT INTO " . DB_PREFIX . "callback SET name = '" . $data['name']  . "', telephone = '" . $data['phone'] . "', date_added = NOW(), date_modified = NOW(), status_id = '0', comment = '" . $this->db->escape($data['comment']) . "'");
	
		return $this->db->getLastId();;
	}	
		
}
?>
