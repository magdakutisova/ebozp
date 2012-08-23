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
	
	public function addRelation($userId, $subsidiaryId){
		try {
			$data['id_user'] = $userId;
			$data['id_subsidiary'] = $subsidiaryId;
			$this->insert($data);
		} catch (Exception $e) {
			//ignorace když se přidávají práva tam, co už přidaná jsou
		}
	}
	
	public function removeRelation($userId, $subsidiaryId){
		$this->delete(array(
			'id_user = ?' => $userId,
			'id_subsidiary = ?' => $subsidiaryId,
		));
	}
	
	public function getByRole($role){
		$select = $this->select ()->from ( 'user_has_subsidiary' )->join('user', 'user.id_user = user_has_subsidiary.id_user')->join('subsidiary', 'subsidiary.id_subsidiary = user_has_subsidiary.id_subsidiary')->where ( 'subsidiary.deleted = 0' )->where('user.role = ?', $role)->order ( array('user.username', 'subsidiary.subsidiary_name'));
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll ( $select );
		return $this->processByRole($result);
	}
	
	private function processByRole($result){
		$processed = array();
		$i = 0;
		foreach ($result as $row){
			$subsidiary = new Application_Model_Subsidiary($row->toArray());
			$processed[$i]['subsidiary'] = $subsidiary;
			$processed[$i]['username'] = $row->username;
			$i++;
		}
		return $processed;
	}
    
}

