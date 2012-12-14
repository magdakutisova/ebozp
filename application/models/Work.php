<?php
class Application_Model_Work{
	
	private $idWork;
	private $work;
	
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
	public function getWork() {
		return $this->work;
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
	public function setWork($work) {
		$this->work = $work;
	}

	public function populate(array $data){
		$this->idWork = isset($data['id_work']) ? $data['id_work'] : null;
		$this->work = isset($data['work']) ? $data['work'] : null;

		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_work'] = $this->idWork;
		}
		$data['work'] = $this->work;
		
		return $data;
	}
	
}