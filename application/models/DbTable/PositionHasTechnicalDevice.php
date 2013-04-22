<?php

class Application_Model_DbTable_PositionHasTechnicalDevice extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_technical_device';
	
	protected $_referenceMap = array(
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
		'TechnicalDevice' => array(
			'columns' => array('id_technical_device'),
			'refTableClass' => 'Application_Model_DbTable_TechnicalDevice',
			'refColumns' => array('id_technical_device'),
		),
	);
	
	public function addRelation($positionId, $technicalDeviceId){
		try{
			$data['id_position'] = $positionId;
			$data['id_technical_device'] = $technicalDeviceId;
			$this->insert($data);
		}
		catch(Exception $e){
			//porušení integrity se ignoruje
		}
	}
	
}