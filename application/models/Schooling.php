<?php
class Application_Model_Schooling{
	
	private $idSchooling;
	private $schooling;
	
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

	public function populate(array $data){
		$this->idSchooling = isset($data['id_schooling']) ? $data['id_schooling'] : null;
		$this->schooling = isset($data['schooling']) ? $data['schooling'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_schooling'] = $this->id_schooling;
		}
		$data['schooling'] = $this->schooling;
		
		return $data;
	}
	
}