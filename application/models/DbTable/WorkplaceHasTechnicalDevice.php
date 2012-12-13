<?php
class Application_Model_DbTable_WorkplaceHasTechnicalDevice extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_technical_device';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'TechnicalDevice' => array(
			'columns' => array('id_technical_device'),
			'refTableClass' => 'Application_Model_DbTable_TechnicalDevice',
			'refColumns' => array('id_technical_device'),
		),
	);
	
	public function addRelation($workplaceId, $technicalDeviceId){
		try{
			$data['id_workplace'] = $workplaceId;
			$data['id_technical_device'] = $technicalDeviceId;
			$this->insert($data);
		}
		catch (Exception $e){
			//porušení integrity se ignoruje
		}
	}
	
	public function removeRelation($workplaceId, $technicalDeviceId){
		$this->delete(array(
			'id_workplace = ?' => $workplaceId,
			'id_technical_device = ?' => $technicalDeviceId,
		));
	}
	
}