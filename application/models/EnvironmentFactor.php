<?php
class Application_Model_EnvironmentFactor{
	
	private $idEnvironmentFactor;
	private $factor;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idEnvironmentFactor
	 */
	public function getIdEnvironmentFactor() {
		return $this->idEnvironmentFactor;
	}

	/**
	 * @return the $factor
	 */
	public function getFactor() {
		return $this->factor;
	}
	
	/**
	 * @param $idEnvironmentFactor the $idEnvironmentFactor to set
	 */
	public function setIdEnvironmentFactor($idEnvironmentFactor) {
		$this->idEnvironmentFactor = $idEnvironmentFactor;
	}

	/**
	 * @param $factor the $factor to set
	 */
	public function setFactor($factor) {
		$this->factor = $factor;
	}

	public function populate(array $data){
		$this->idEnvironmentFactor = isset($data['id_environment_factor']) ? $data['id_environment_factor'] : null;
		$this->factor = isset($data['factor']) ? $data['factor'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_environment_factor'] = $this->idEnvironmentFactor;
		}
		$data['factor'] = $this->factor;
		
		return $data;
	}
	
}