<?php
class Application_Model_DbTable_WorkplaceHasPosition extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_position';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
	);
	
	public function addRelation($workplaceId, $positionId){
		$data['id_workplace'] = $workplaceId;
		$data['id_position'] = $positionId;
		$this->insert($data); 
	}
	
}