<?php
class Application_Model_DbTable_Responsibility extends Zend_Db_Table_Abstract{
	
	protected $_name = 'responsibility';
	
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
			);
	
}