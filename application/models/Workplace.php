<?php
class Application_Model_Workplace{
	
	private $idWorkplace;
	private $name;
	private $description;
	private $subsidiaryId;
	private $note;
	private $private;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idWorkplace
	 */
	public function getIdWorkplace() {
		return $this->idWorkplace;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return the $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return the $subsidiaryId
	 */
	public function getSubsidiaryId() {
		return $this->subsidiaryId;
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
	 * @param $idWorkplace the $idWorkplace to set
	 */
	public function setIdWorkplace($idWorkplace) {
		$this->idWorkplace = $idWorkplace;
	}

	/**
	 * @param $name the $name to set
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param $description the $description to set
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @param $subsidiaryId the $subsidiaryId to set
	 */
	public function setSubsidiaryId($subsidiaryId) {
		$this->subsidiaryId = $subsidiaryId;
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

	public function populate(array $data){
		$this->idWorkplace = isset($data['id_workplace']) ? $data['id_workplace'] : null;
		$this->name = isset($data['name']) ? $data['name'] : null;
		$this->description = isset($data['description']) ? $data['description'] : null;
		$this->subsidiaryId = isset($data['subsidiary_id']) ? $data['subsidiary_id'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_workplace'] = $this->idWorkplace;
		}
		$data['name'] = $this->name;
		$data['description'] = $this->description;
		$data['subsidiary_id'] = $this->subsidiaryId;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		
		return $data;
	}
	
}