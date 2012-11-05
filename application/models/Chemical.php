<?php
class Application_Model_Chemical{
	
	private $idChemical;
	private $chemical;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idChemical
	 */
	public function getIdChemical() {
		return $this->idChemical;
	}

	/**
	 * @return the $chemical
	 */
	public function getChemical() {
		return $this->chemical;
	}

	/**
	 * @param $idChemical the $idChemical to set
	 */
	public function setIdChemical($idChemical) {
		$this->idChemical = $idChemical;
	}

	/**
	 * @param $chemical the $chemical to set
	 */
	public function setChemical($chemical) {
		$this->chemical = $chemical;
	}

	public function populate(array $data){
		$this->idChemical = isset($data['id_chemical']) ? $data['id_chemical'] : null;
		$this->chemical = isset($data['chemical']) ? $data['chemical'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_chemical'] = $this->idChemical;
		}
		$data['chemical'] = $this->chemical;
		
		return $data;
	}
	
}