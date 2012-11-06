<?php
class Application_Model_DbTable_WorkplaceHasChemical extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_chemical';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'Chemical' => array(
			'columns' => array('id_chemical'),
			'refTableClass' => 'Application_Model_DbTable_Chemical',
			'refColumns' => array('id_chemical'),
		),
	);
	
	public function addRelation($workplaceId, $chemicalId){
		$data['id_workplace'] = $workplaceId;
		$data['id_chemical'] = $chemicalId;
		$this->insert($data);
	}
	
}