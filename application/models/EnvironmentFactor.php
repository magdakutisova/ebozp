<?php
class Application_Model_EnvironmentFactor{
	
	private $idEnvironmentFactor;
	private $factor;
	private $category;
	private $protectionMeasures;
	private $measurementTaken;
	private $note;
	private $private;
	private $positionId;
	
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
	 * @return the $category
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @return the $protectionMeasures
	 */
	public function getProtectionMeasures() {
		return $this->protectionMeasures;
	}

	/**
	 * @return the $measurementTaken
	 */
	public function getMeasurementTaken() {
		return $this->measurementTaken;
	}

	/**
	 * @return the $note
	 */
	public function getNote() {
		return $this->note;
	}

	/**
	 * @return the $private
	 */
	public function getPrivate() {
		return $this->private;
	}

	/**
	 * @return the $positionId
	 */
	public function getPositionId() {
		return $this->positionId;
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

	/**
	 * @param $category the $category to set
	 */
	public function setCategory($category) {
		$this->category = $category;
	}

	/**
	 * @param $protectionMeasures the $protectionMeasures to set
	 */
	public function setProtectionMeasures($protectionMeasures) {
		$this->protectionMeasures = $protectionMeasures;
	}

	/**
	 * @param $measurementTaken the $measurementTaken to set
	 */
	public function setMeasurementTaken($measurementTaken) {
		$this->measurementTaken = $measurementTaken;
	}

	/**
	 * @param $note the $note to set
	 */
	public function setNote($note) {
		$this->note = $note;
	}

	/**
	 * @param $private the $private to set
	 */
	public function setPrivate($private) {
		$this->private = $private;
	}

	/**
	 * @param $positionId the $positionId to set
	 */
	public function setPositionId($positionId) {
		$this->positionId = $positionId;
	}

	public function populate(array $data){
		$this->idEnvironmentFactor = isset($data['id_environment_factor']) ? $data['id_environment_factor'] : null;
		$this->factor = isset($data['factor']) ? $data['factor'] : null;
		$this->category = isset($data['category']) ? $data['category'] : null;
		$this->protectionMeasures = isset($data['protection_measures']) ? $data['protection_measures'] : null;
		$this->measurementTaken = isset($data['measurement_taken']) ? $data['measurement_taken'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		$this->positionId = isset($data['position_id']) ? $data['position_id'] : null;

		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_environment_factor'] = $this->idEnvironmentFactor;
		}
		$data['factor'] = $this->factor;
		$data['category'] = $this->category;
		$data['protection_measures'] = $this->protectionMeasures;
		$data['measurement_taken'] = $this->measurementTaken;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		$data['position_id'] = $this->positionId;
		
		return $data;
	}
	
}