<?php
class Application_Model_Position{
	
	private $idPosition;
	private $position;
	private $workingHours;
	private $categorization;
	private $note;
	private $private;
	private $subsidiaryId;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idPosition
	 */
	public function getIdPosition() {
		return $this->idPosition;
	}

	/**
	 * @return the $position
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @return the $workingHours
	 */
	public function getWorkingHours() {
		return $this->workingHours;
	}

	/**
	 * @return the $categorization
	 */
	public function getCategorization() {
		return $this->categorization;
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
	
	public function getSubsidiaryId(){
		return $this->subsidiaryId;
	}

	/**
	 * @param $idPosition the $idPosition to set
	 */
	public function setIdPosition($idPosition) {
		$this->idPosition = $idPosition;
	}

	/**
	 * @param $position the $name to set
	 */
	public function setPosition($position) {
		$this->position = $position;
	}

	/**
	 * @param $workingHours the $workingHours to set
	 */
	public function setWorkingHours($workingHours) {
		$this->workingHours = $workingHours;
	}

	/**
	 * @param $categorization the $categorization to set
	 */
	public function setCategorization($categorization) {
		$this->categorization = $categorization;
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
	
	public function setSubsidiaryId($subsidiaryId){
		$this->subsidiaryId = $subsidiaryId;
	}

	public function populate(array $data){
		$this->idPosition = isset($data['id_position']) ? $data['id_position'] : null;
		$this->position = isset($data['position']) ? $data['position'] : null;
		$this->workingHours = isset($data['working_hours']) ? $data['working_hours'] : null;
		$this->categorization = isset($data['categorization']) ? $data['categorization'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		$this->subsidiaryId = isset($data['subsidiary_id']) ? $data['subsidiary_id'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_position'] = $this->idPosition;
		}
		$data['position'] = $this->position;
		$data['working_hours'] = $this->workingHours;
		$data['categorization'] = $this->categorization;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		$data['subsidiary_id'] = $this->subsidiaryId;
		
		return $data;
	}
	
}