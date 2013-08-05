<?php
class Application_Model_Doctor{
	
	private $idDoctor;
	private $name;
	private $street;
	private $town;
	private $subsidiaryId;
	
	public function __construct($options = array()){
		if(!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idDoctor
	 */
	public function getIdDoctor() {
		return $this->idDoctor;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return the $street
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * @return the $town
	 */
	public function getTown() {
		return $this->town;
	}

	/**
	 * @return the $subsidiaryId
	 */
	public function getSubsidiaryId() {
		return $this->subsidiaryId;
	}

	/**
	 * @param field_type $idDoctor
	 */
	public function setIdDoctor($idDoctor) {
		$this->idDoctor = $idDoctor;
	}

	/**
	 * @param field_type $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param field_type $street
	 */
	public function setStreet($street) {
		$this->street = $street;
	}

	/**
	 * @param field_type $town
	 */
	public function setTown($town) {
		$this->town = $town;
	}

	/**
	 * @param field_type $subsidiaryId
	 */
	public function setSubsidiaryId($subsidiaryId) {
		$this->subsidiaryId = $subsidiaryId;
	}

	public function populate(array $data){
		$this->idDoctor = $data['id_doctor'];
		$this->name = $data['name'];
		$this->street = $data['street'];
		$this->town = $data['town'];
		$this->subsidiaryId = $data['subsidiary_id'];
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_doctor'] = $this->idDoctor;
		}
		$data['name'] = $this->name;
		$data['street'] = $this->street;
		$data['town'] = $this->town;
		$data['subsidiary_id'] = $this->subsidiaryId;
			
		return $data;
	}
	
}