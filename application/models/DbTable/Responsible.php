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
	
}