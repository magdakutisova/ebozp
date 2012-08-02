<?php

class Application_Model_DbTable_UserHasSubsidiary extends Zend_Db_Table_Abstract
{

    protected $_name = 'user_has_subsidiary';

	protected $_referenceMap = array(
		'Subsidiary' => array(
			'columns' => array('id_subsidiary'),
			'refTableClass' => 'Application_Model_DbTable_Subsidiary',
			'refColumns' => array('id_subsidiary'),
		),
		'User' => array(
			'columns' => array('id_user'),
			'refTableClass' => 'Application_Model_DbTable_User',
			'refColumns' => array('id_user'),
		),
	);
    
}

