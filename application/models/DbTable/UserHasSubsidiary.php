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
	
	public function getByRole($role, $archived = 0, $active = null){
		if($active !== null){
			$select = $this->select ()
			->from ( 'user_has_subsidiary' )
			->join('user', 'user.id_user = user_has_subsidiary.id_user')
			->join('subsidiary', 'subsidiary.id_subsidiary = user_has_subsidiary.id_subsidiary')
			->join('client', 'subsidiary.client_id = client.id_client')
			->where('client.archived = ?', $archived)
			->where('subsidiary.active = ?', $active)
			->where ( 'subsidiary.deleted = 0' )
			->where('user.role = ?', $role)
			->order ( array('user.username', 'subsidiary.subsidiary_town', 'subsidiary.subsidiary_street'));
		}
		else{
			$select = $this->select ()
			->from ( 'user_has_subsidiary' )
			->join('user', 'user.id_user = user_has_subsidiary.id_user')
			->join('subsidiary', 'subsidiary.id_subsidiary = user_has_subsidiary.id_subsidiary')
			->join('client', 'subsidiary.client_id = client.id_client')
			->where('client.archived = ?', $archived)
			->where ( 'subsidiary.deleted = 0' )
			->where('user.role = ?', $role)
			->order ( array('user.username', 'subsidiary.subsidiary_town', 'subsidiary.subsidiary_street'));
		}
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll ( $select );
		return $this->processByRole($result);
	}
	
	/****************************************
	 * Zatím se používá pro výpis techniků oddělených čárkami.
	 */
	public function getByRoleAndSubsidiary($role, $subsidiaryId){
		$select = $this->select()->from('user_has_subsidiary')->join('user', 'user.id_user = user_has_subsidiary.id_user')->where('user.role = ?', $role)->where('user_has_subsidiary.id_subsidiary = ?', $subsidiaryId);
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		$processed = array();
		if (count($result) != 0){		
			foreach($result as $row){
				$processed[] = $row->name;
			}
		}
		return implode(', ', $processed);
	}
	
	public function getByRoleAndUsername($role, $username){
		$select = $this->select()->from('user_has_subsidiary')->join('user', 'user.id_user = user_has_subsidiary.id_user')->join('subsidiary', 'subsidiary.id_subsidiary = user_has_subsidiary.id_subsidiary')->where('user.role = ?', $role)->where('user.username = ?', $username)->order(array("subsidiary.hq desc", 'subsidiary.subsidiary_name', 'subsidiary_town', 'subsidiary_street'));
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
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

