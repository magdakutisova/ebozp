<?php
class Application_Model_Schooling{
	
	private $idSchooling;
	private $schooling;
	private $lastExecution;
	private $note;
	private $private;
	private $positionId;
	
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
	 * @return the $lastExecution
	 */
	public function getLastExecution() {
		return $this->lastExecution;
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

	/**
	 * @param field_type $lastExecution
	 */
	public function setLastExecution($lastExecution) {
		$this->lastExecution = $lastExecution;
	}

	/**
	 * @param field_type $note
	 */
	public function setNote($note) {
		$this->note = $note;
	}

	/**
	 * @param field_type $private
	 */
	public function setPrivate($private) {
		$this->private = $private;
	}

	/**
	 * @param field_type $positionId
	 */
	public function setPositionId($positionId) {
		$this->positionId = $positionId;
	}

	public function populate(array $data){
		$this->idSchooling = isset($data['id_schooling']) ? $data['id_schooling'] : null;
		$this->schooling = isset($data['schooling']) ? $data['schooling'] : null;
		$this->lastExecution = isset($data['last_execution']) ? $data['last_execution'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		$this->positionId = isset($data['position_id']) ? $data['position_id'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_schooling'] = $this->id_schooling;
		}
		$data['schooling'] = $this->schooling;
		$data['last_execution'] = $this->lastExecution;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		$data['position_id'] = $this->positionId;
		
		return $data;
	}
	
}