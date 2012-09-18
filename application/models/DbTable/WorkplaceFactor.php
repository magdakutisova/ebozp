<?php
class Application_Model_DbTable_WorkplaceFactor extends Zend_Db_Table_Abstract {
	
	protected $_name = 'workplace_factor';
	
	protected $_referenceMap = array(
    	'Workplace' => array(
    		'columns' => 'workplace_id',
    		'refTableClass' => 'Application_Model_DbTable_Workplace',
    		'refColumns' => 'id_workplace'
    	),
    );
	
	public function getWorkplaceFactor($id){
		$id = (int) $id;
		$row = $this->fetchRow('id_workplace_factor = ' . $id);
		if (!$row){
			throw new Exception("Faktor pracovního prostředí $id nebyl nalezen.");
		}
		$workplaceFactor = $row->toArray();
		return new Application_Model_WorkplaceFactor($workplaceFactor);
	}
	
	public function addWorkplaceFactor(Application_Model_WorkplaceFactor $workplaceFactor){
		$data = $workplaceFactor->toArray();
		$workplaceFactorId = $this->insert($data);
		return $workplaceFactorId;
	}
	
	public function updateWorkplaceFactor(Application_Model_WorkplaceFactor $workplaceFactor){
		$data = $workplaceFactor->toArray();
		$this->update($data, 'id_workplace_factor = ' . $workplaceFactor->getIdWorkplaceFactor());
	}
	
	public function deleteWorkplaceFactor($id){
		$this->delete('id_workplace_factor = ' . (int)$id);
	}
	
}