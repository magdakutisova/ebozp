<?php
class Application_Model_Responsibility{
	
	private $idResponsibility;
	private $responsibility;
	private $clientId;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idResponsibility
	 */
	public function getIdResponsibility() {
		return $this->idResponsibility;
	}

	/**
	 * @return the $responsibility
	 */
	public function getResponsibility() {
		return $this->responsibility;
	}

	/**
	 * @return the $clientId
	 */
	public function getClientId() {
		return $this->clientId;
	}

	/**
	 * @param field_type $idResponsibility
	 */
	public function setIdResponsibility($idResponsibility) {
		$this->idResponsibility = $idResponsibility;
	}

	/**
	 * @param field_type $responsibility
	 */
	public function setResponsibility($responsibility) {
		$this->responsibility = $responsibility;
	}

	/**
	 * @param field_type $clientId
	 */
	public function setClientId($clientId) {
		$this->clientId = $clientId;
	}

	public function populate(array $data){
		$this->idResponsibility = isset($data['id_responsibility']) ? $data['id_responsibility'] : null;
		$this->responsibility = isset($data['responsibility']) ? $data['responsibility'] : null;
		$this->clientId = isset($data['client_id']) ? $data['client_id'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_responsibility'] = $this->idResponsibility;
		}
		$data['responsibility'] = $this->responsibility;
		$data['client_id'] = $this->clientId;
		
		return $data;
	}
	
}