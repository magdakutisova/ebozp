<?php
class Application_Model_DbTable_Responsible extends Zend_Db_Table_Abstract{
	
	protected $_name = 'responsible';
	
	protected $_referenceMap = array(
			'Subsidiary' => array(
					'columns' => array('id_subsidiary'),
					'refTableClass' => 'Application_Model_DbTable_Subsidiary',
					'refColumns' => array('id_subsidiary'),
					),
			'Employee' => array(
					'columns' => array('id_employee'),
					'refTableClass' => 'Application_Model_DbTable_Employee',
					'refColumns' => array('id_employee'),
					),
			'Responsibility' => array(
					'columns' => array('id_responsibility'),
					'refTableClass' => 'Application_Model_DbTable_Responsibility',
					'refColumns' => array('id_responsibility'),
					),
			);
	
	public function addRelation($responsibilityId, $employeeId, $subsidiaryId){
		try{
			$data['id_responsibility'] = $responsibilityId;
			$data['id_employee'] = $employeeId;
			$data['id_subsidiary'] = $subsidiaryId;
			$this->insert($data);
		}
		catch(Exception $e){
			//porušení integrity se ignoruje
		}
	}
	
	public function removeResponsibles($subsidiaryId){
		$this->delete('id_subsidiary = ' . (int)$subsidiaryId);
	}
	
}