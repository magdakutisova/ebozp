<?php
class Application_Model_WorkplaceRisk{
	
	private $idWorkplaceRisk;
	private $risk;
	private $note;
	private $workplaceId;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idWorkplaceRisk
	 */
	public function getIdWorkplaceRisk() {
		return $this->idWorkplaceRisk;
	}

	/**
	 * @return the $risk
	 */
	public function getRisk() {
		return $this->risk;
	}

	/**
	 * @return the $note
	 */
	public function getNote() {
		return $this->note;
	}

	/**
	 * @return the $workplaceId
	 */
	public function getWorkplaceId() {
		return $this->workplaceId;
	}

	/**
	 * @param $idWorkplaceRisk the $idWorkplaceRisk to set
	 */
	public function setIdWorkplaceRisk($idWorkplaceRisk) {
		$this->idWorkplaceRisk = $idWorkplaceRisk;
	}

	/**
	 * @param $risk the $risk to set
	 */
	public function setRisk($risk) {
		$this->risk = $risk;
	}

	/**
	 * @param $note the $note to set
	 */
	public function setNote($note) {
		$this->note = $note;
	}

	/**
	 * @param $workplaceId the $workplaceId to set
	 */
	public function setWorkplaceId($workplaceId) {
		$this->workplaceId = $workplaceId;
	}

	public function populate(array $data){
		$this->idWorkplaceRisk = isset($data['id_workplace_risk']) ? $data['id_workplace_risk'] : null;
		$this->risk = isset($data['risk']) ? $data['risk'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->workplaceId = isset($data['workplace_id']) ? $data['workplace_id'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_workplace_risk'] = $this->idWorkplaceRisk;
		}
		$data['risk'] = $this->risk;
		$data['note'] = $this->note;
		$data['workplace_id'] = $this->workplaceId;
	}
	
}