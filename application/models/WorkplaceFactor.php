<?php
class Application_Model_WorkplaceFactor{
	
	private $idWorkplaceFactor;
	private $factor;
	private $note;
	private $workplaceId;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idWorkplaceFactor
	 */
	public function getIdWorkplaceFactor() {
		return $this->idWorkplaceFactor;
	}

	/**
	 * @return the $factor
	 */
	public function getFactor() {
		return $this->factor;
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
	 * @param $idWorkplaceFactor the $idWorkplaceFactor to set
	 */
	public function setIdWorkplaceFactor($idWorkplaceFactor) {
		$this->idWorkplaceFactor = $idWorkplaceFactor;
	}

	/**
	 * @param $factor the $factor to set
	 */
	public function setFactor($factor) {
		$this->factor = $factor;
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
		$this->idWorkplaceFactor = isset($data['id_workplace_factor']) ? $data['id_workplace_factor'] : null;
		$this->factor = isset($data['factor']) ? $data['factor'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->workplaceId = isset($data['workplace_id']) ? $data['workplace_id'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if (!$toUpdate){
			$data['id_workplace_factor'] = $this->idWorkplaceFactor;
		}
		$data['factor'] = $this->factor;
		$data['note'] = $this->note;
		$data['workplace_id'] = $this->workplaceId;
		
		return $data;
	}
	
}