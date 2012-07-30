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
    
	public function getUsernames(){
		$select = $this->select()->from('user')->columns(array('id_user', 'username'));
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
	
}

