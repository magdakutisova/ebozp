<?php

class Application_Model_DbTable_PositionHasEmployee extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_employee';
	
	protected $_relationMap = array(
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
		'Employee' => array(
			'columns' => array('id_employee'),
			'refTableClass' => 'Application_Model_DbTable_Employee',
			'refColumns' => array('id_employee'),
		),
	);
	
}