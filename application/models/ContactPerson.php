<?php
class Application_Model_ContactPerson{
	
	private $idContactPerson;
	private $name;
	private $phone;
	private $email;
	private $subsidiaryId;
	
	public function __construct($options = array()){
		if(!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idContactPerson
	 */
	public function getIdContactPerson() {
		return $this->idContactPerson;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return the $phone
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * @return the $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return the $subsidiaryId
	 */
	public function getSubsidiaryId() {
		return $this->subsidiaryId;
	}

	/**
	 * @param field_type $idContactPerson
	 */
	public function setIdContactPerson($idContactPerson) {
		$this->idContactPerson = $idContactPerson;
	}

	/**
	 * @param field_type $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param field_type $phone
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
	}

	/**
	 * @param field_type $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @param field_type $subsidiaryId
	 */
	public function setSubsidiaryId($subsidiaryId) {
		$this->subsidiaryId = $subsidiaryId;
	}

	public function populate(array $data){
		$this->idContactPerson = $data['id_contact_person'];
		$this->name = $data['name'];
		$this->phone = $data['phone'];
		$this->email = $data['email'];
		$this->subsidiaryId = $data['subsidiary_id'];
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_contact_person'] = $this->idContactPerson;
		}
		$data['name'] = $this->name;
		$data['phone'] = $this->phone;
		$data['email'] = $this->email;
		$data['subsidiary_id'] = $this->subsidiaryId;
			
		return $data;
	}
		
}