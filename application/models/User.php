<?php
class Application_Model_User implements Zend_Acl_Role_Interface{
	
	private $idUser;
	private $username;
	private $name;
	private $password;
	private $salt;
	private $role;
	private $userSubsidiaries;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idUser
	 */
	public function getIdUser() {
		return $this->idUser;
	}

	/**
	 * @param $idUser the $idUser to set
	 */
	public function setIdUser($idUser) {
		$this->idUser = $idUser;
	}

	/**
	 * @return the $username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param $username the $username to set
	 */
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}

	/**
	 * @return the $password
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param $password the $password to set
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * @return the $salt
	 */
	public function getSalt() {
		return $this->salt;
	}

	/**
	 * @param $salt the $salt to set
	 */
	public function setSalt($salt) {
		$this->salt = $salt;
	}

	/**
	 * @return the $role
	 */
	public function getRole() {
		return $this->role;
	}

	/**
	 * @param $role the $role to set
	 */
	public function setRole($role) {
		$this->role = $role;
	}
	
	public function getRoleId(){
		return $this->getRole();
	}

	/**********************************************************************
	 * Nastaví atributy třídy z pole. User_subsidiaries se plní funkcí wrap_up_user v modelu tabulky user.
	 */
	public function populate(array $data){
		
		$this->idUser = isSet($data['id_user']) ? $data['id_user'] : null;
		$this->username = isSet($data['username']) ? $data['username'] : null;
		$this->password = isSet($data['password']) ? $data['password'] : null;
		$this->salt = isSet($data['salt']) ? $data['salt'] : null;
		$this->role = isSet($data['role']) ? $data['role'] : null;
		$this->name = isSet($data['name']) ? $data['name'] : null;
		
		$this->userSubsidiaries = array();
		$subsidiaries = isSet($data['user_subsidiaries']) ? $data['user_subsidiaries'] : null;
		if ($subsidiaries != null){
			$this->addSubsidiaryToUser($subsidiaries);
		}
		return $this;
	}
	
	public function toArray($toUpdate = false, $withSubsidiaries = true){
		if (!$toUpdate){
			$data['id_user'] = $this->idUser;
		}
		$data['username'] = $this->username;
		$data['password'] = $this->password;
		$data['salt'] = $this->salt;
		$data['role'] = $this->role;
		$data['name'] = $this->name;
		
		if($withSubsidiaries){
			$data['user_subsidiaries'] = $this->userSubsidiaries;
		}
		
		return $data;
	}
	
	public function hasSubsidiary($subsidiary){
		if($subsidiary instanceOf Application_Model_Subsidiary){
			$subsidiary = $subsidiary->getIdSubsidiary();
		}
		
		return in_array($subsidiary, $this->getUserSubsidiaries());
	}
	
	public function getUserSubsidiaries(){
		return $this->userSubsidiaries;
	}
	
	/***************
	 * Přidá k uživateli ID poboček z pole poboček. Volá se z populate.
	 */
	public function addSubsidiaryToUser($subsidiary){
		if(is_array($subsidiary)){
			foreach($subsidiary as $sub){
				$this->addSubsidiaryToUser($sub);
			}
		} elseif ($subsidiary instanceof Application_Model_Subsidiary){
			$this->userSubsidiaries[] = $subsidiary->getIdSubsidiary();
		} elseif (is_numeric($subsidiary)){
			$this->userSubsidiaries[] = $subsidiary;
		} else{
			throw new Exception('Invalid subsidiary provided.');
		}
		
		return $this;
	}
	
}