<?php
class Application_Model_Work{
	
	private $idWork;
	private $name;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idWork
	 */
	public function getIdWork() {
		return $this->idWork;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param $idWork the $idWork to set
	 */
	public function setIdWork($idWork) {
		$this->idWork = $idWork;
	}

	/**
	 * @param $name the $name to set
	 */
	public function setName($name) {
		$this->name = $name;
	}

	public function populate(array $data){
		$this->idWork = isset($data['id_workplace']) ? $data['id_workplace'] : null;
		$this->name = isset($data['name']) ? $data['name'] : null;

		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_work'] = $this->idWork;
		}
		$data['name'] = $this->name;
		
		return $data;
	}
	
}