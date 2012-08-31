<?php
class Application_Model_DbTable_Workplace extends Zend_Db_Table_Abstract {
	
	protected $_name = 'workplace';
	
	public function getWorkplace($id){
		$id = (int) $id;
		$row = $this->fetchRow('id_workplace = ' . $id);
		if (!$row){
			throw new Exception("Pracoviště $id nebylo nalezeno.");
		}
		$workplace = $row->toArray();
		return new Application_Model_Workplace($workplace);
	}
	
	public function addWorkplace(Application_Model_Workplace $workplace){
		$data = $workplace->toArray();
		$workplaceId = $this->insert($data);
		return $workplaceId;
	}
	
	public function updateWorkplace(Application_Model_Workplace $workplace){
		$data = $workplace->toArray();
		$this->update($data, 'id_workplace = ' . $workplace->getIdWorkplace());
	}
	
	public function deleteWorkplace($id){
		$this->delete('id_workplace = ' . (int)$id);
	}
	
}