<?php
class Application_Model_DbTable_WorkplaceRisk extends Zend_Db_Table_Abstract {
	
	protected $_name = 'workplace_risk';
	
	protected $_referenceMap = array(
    	'Workplace' => array(
    		'columns' => 'workplace_id',
    		'refTableClass' => 'Application_Model_DbTable_Workplace',
    		'refColumns' => 'id_workplace'
    	),
    );
	
	public function getWorkplaceRisk($id){
		$id = (int) $id;
		$row = $this->fetchRow('id_workplace_risk = ' . $id);
		if (!$row){
			throw new Exception("Riziko $id nebylo nalezeno.");
		}
		$workplaceRisk = $row->toArray();
		return new Application_Model_WorkplaceRisk($workplaceRisk);
	}
	
	public function addWorkplaceRisk(Application_Model_WorkplaceRisk $workplaceRisk){
		$data = $workplaceRisk->toArray();
		$workplaceRiskId = $this->insert($data);
		return $workplaceRiskId;
	}
	
	public function updateWorkplaceRisk(Application_Model_WorkplaceRisk $workplaceRisk){
		$data = $workplaceRisk->toArray();
		$this->update($data, 'id_workplace_risk = ' . $workplaceRisk->getIdWorkplaceRisk());
	}
	
	public function deleteWorkplaceRisk($id){
		$this->delete('id_workplace_risk = ' . (int)$id);
	}
	
}