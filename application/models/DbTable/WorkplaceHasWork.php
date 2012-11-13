<?php
class Application_Model_DbTable_WorkplaceHasWork extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_work';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'Work' => array(
			'columns' => array('id_work'),
			'refTableClass' => 'Application_Model_DbTable_Work',
			'refColumns' => array('id_work'),
		),
	);
	
	public function addRelation($workplaceId, $workId){
		try{
			$data['id_workplace'] = $workplaceId;
			$data['id_work'] = $workId;
			$this->insert($data);
		}
		catch(Exception $e){
			//porušení integrity se ignoruje
		}
	}
	
}