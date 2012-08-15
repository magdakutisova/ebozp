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
    	return $this->wrapUpUser($row);
    }
    
    public function getByUsername($username){
    	$row = $this->fetchRow('username = "' . $username . '"');
    	if(!$row){
    		return null;
    	}
    	return $this->wrapUpUser($row);
    }

	public function addUser($user){
		$data = $user->toArray();
		$userId = $this->insert($data);
		return $userId;
	}
	
	public function updateUser($user){
		$data = $user->toArray();
		$this->update($data, 'id_user = ' . $user->getIdUser());
	}
	
	public function deleteUser($userId){
		$this->delete('id_user = ' . (int) $userId);
	}
    
	/****************************
	 * Vrací seznam uživatelských jmen pro combobox.
	 */
	public function getUsernames(){
		$select = $this->select()->from('user')->columns(array('id_user', 'username'))->where('role != 1')->order('username ASC');
		$results = $this->fetchAll($select);
		if (count ( $results ) > 0) {
			$usernames = array ();
			foreach ( $results as $result ) :
				$key = $result->id_user;
				$username = $result->username;
				$usernames [$key] = $username;
			endforeach
			;
			return $usernames;
		} else {
			return 0;
		}
	}
	
	public function updatePassword($username, $password, $salt){
		$user = $this->fetchRow('username = "' . $username . '"');
		$user->password = $password;
		$user->salt = $salt;
		$user->save();
	}
	
	/********************************************************
	 * Přidává k uživateli pole ID poboček.
	 */
	private function wrapUpUser(Zend_Db_Table_Row $user){
		$subsidiaries = $user->findDependentRowset('Application_Model_DbTable_UserHasSubsidiary');
		
		$data = $user->toArray();
		$data['user_subsidiaries'] = array();
		foreach($subsidiaries as $subsidiary){
			$data['user_subsidiaries'][] = $subsidiary->id_subsidiary;
		}
		return new Application_Model_User($data);
	}
	
}

