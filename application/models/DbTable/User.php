<?php

class Application_Model_DbTable_User extends Zend_Db_Table_Abstract
{

    protected $_name = 'user';
    
    public function getUser($userId){
    	$userId = (int) $userId;
    	$row = $this->fetchRow('id_user = ' . $userId);
    	if (!$row){
    		throw new Exception("Uživatel $userId nebyl nalezen.");
    	}
    	return $row->toArray();
    }
    
    public function getByUsername($username){
    	$row = $this->fetchRow('username = "' . $username . '"');
    	if(!$row){
    		return null;
    	}
    	return $row->toArray();
    }

	public function addUser($username, $password, $salt, $role){
		$data = array(
			'username' => $username,
			'password' => $password,
			'salt' => $salt,
			'role' => $role,
		);
		$userId = $this->insert($data);
		return $userId;
	}
	
	public function updateUser($userId, $username, $password, $salt, $role){
		$data = array(
			'username' => $username,
			'password' => $password,
			'salt' => $salt,
			'role' => $role,
		);
		$this->update($data, 'id_user = ' . (int) $userId);
	}
	
	public function deleteUser($userId){
		$this->delete('id_user = ' . (int) $userId);
	}
    
}

