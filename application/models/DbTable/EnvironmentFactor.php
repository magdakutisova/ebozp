<?php

class Application_Model_DbTable_EnvironmentFactor extends Zend_Db_Table_Abstract{
	
	protected $_name = 'environment_factor';
	
	protected $_referenceMap = array(
		'Position' => array(
			'columns' => 'position_id',
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => 'id_position',
		),
	);
	
	public function getEnvironmentFactor($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_environment_factor = ' . $id);
		if(!$row){
			throw new Exception("Faktor pracovního prostředí $id nebyl nalezen");
		}
		$environmentFactor = $row->toArray();
		return new Application_Model_EnvironmentFactor($environmentFactor);
	}
	
	public function updateEnvironmentFactor(Application_Model_EnvironmentFactor $environmentFactor){
		$data = $environmentFactor->toArray();
		$this->update($data, 'id_environment_factor = ' . $environmentFactor->getIdEnvironmentFactor());
	}
	
	public function deleteEnvironmentFactor($id){
		$this->delete('id_environment_factor = ' . (int)$id);
	}
	
}