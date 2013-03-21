<?php

class Application_Model_DbTable_PositionHasSchooling extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_schooling';
	
	protected $_referenceMap = array(
			'Position' => array(
					'columns' => array('id_position'),
					'refTableClass' => 'Application_Model_DbTable_Position',
					'refColumns' => array('id_position'),
					),
			'Schooling' => array(
					'columns' => array('id_schooling'),
					'refTableClass' => 'Application_Model_DbTable_Schooling',
					'refColumns' => array('id_schooling'),
					),
			);
	
}