<?php
class Application_Model_Schooling{
	
	private $idSchooling;
	private $schooling;
	private $clientId;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idSchooling
	 */
	public function getIdSchooling() {
		return $this->idSchooling;
	}

	/**
	 * @return the $name
	 */
	public function getSchooling() {
		return $this->schooling;
	}
	
	public function getClientId(){
		return $this->clientId;
	}

	/**
	 * @param field_type $idSchooling
	 */
	public function setIdSchooling($idSchooling) {
		$this->idSchooling = $idSchooling;
	}

	/**
	 * @param field_type $name
	 */
	public function setSchooling($schooling) {
		$this->schooling = $schooling;
	}
	
	public function setClientId($clientId){
		$this->clientId = $clientId;
	}

	public function populate(array $data){
		$this->idSchooling = isset($data['id_schooling']) ? $data['id_schooling'] : null;
		$this->schooling = isset($data['schooling']) ? $data['schooling'] : null;
		$this->clientId = isset($data['client_id']) ? $data['client_id'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_schooling'] = $this->id_schooling;
		}
		$data['schooling'] = $this->schooling;
		$data['client_id'] = $this->clientId;
		
		return $data;
	}
	
}