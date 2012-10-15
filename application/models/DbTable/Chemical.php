<?php
class Application_Model_DbTable_Chemical extends Zend_Db_Table_Abstract{
	
	protected $_name = 'chemical';
	
	public function getChemical($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_chemical = ' . $id);
		if(!$row){
			throw new Exception("Chemická látka $id nebyla nalezena.");
		}
		$chemical = $row->toArray();
		return new Application_Model_Chemical($chemical);
	}
	
}