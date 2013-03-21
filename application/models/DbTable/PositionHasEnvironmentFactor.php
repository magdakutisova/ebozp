<?php

class Application_Model_DbTable_PositionHasEnvironmentFactor extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_environment_factor';
	
	protected $_referenceMap = array(
			'Position' => array(
					'columns' => array('id_position'),
					'refTableClass' => 'Application_Model_DbTable_Position',
					'refColumns' => array('id_position'),
					),
			'EnvironmentFactor' => array(
					'columns' => array('id_environment_factor'),
					'refTableClass' => 'Application_Model_DbTable_EnvironmentFactor',
					'refColumns' => array('id_environment_factor'),
					),
			);
	
}