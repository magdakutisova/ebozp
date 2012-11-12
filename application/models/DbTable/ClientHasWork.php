<?php
class Application_Model_DbTable_ClientHasWork extends Zend_Db_Table_Abstract{
	
	protected $_name = 'client_has_work';
	
	protected $_referenceMap = array(
		'Client' => array(
			'columns' => array('id_client'),
			'refTableClass' => 'Application_Model_DbTable_Client',
			'refColumns' => array('id_client'),
		),
		'Work' => array(
			'columns' => array('id_work'),
			'refTableClass' => 'Application_Model_DbTable_Work',
			'refColumns' => array('id_work'),
		),
	);
	
	public function addRelation($clientId, $workId){
		try{
			$data['id_client'] = $clientId;
			$data['id_work'] = $workId;
			$this->insert($data);
		}
		catch(Exception $e){
			//znova není potřeba vkládat
		}
	}
	
}