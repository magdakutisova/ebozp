<?php
class Application_Model_TechnicalDevice{
	
	private $idTechnicalDevice;
	private $sort;
	private $type;
	private $note;
	private $private;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idTechnicalDevice
	 */
	public function getIdTechnicalDevice() {
		return $this->idTechnicalDevice;
	}

	/**
	 * @return the $sort
	 */
	public function getSort() {
		return $this->sort;
	}

	/**
	 * @return the $type
	 */
	public function getType() {
		return $this->type;
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
	 * @param $idTechnicalDevice the $idTechnicalDevice to set
	 */
	public function setIdTechnicalDevice($idTechnicalDevice) {
		$this->idTechnicalDevice = $idTechnicalDevice;
	}

	/**
	 * @param $sort the $sort to set
	 */
	public function setSort($sort) {
		$this->sort = $sort;
	}

	/**
	 * @param $type the $type to set
	 */
	public function setType($type) {
		$this->type = $type;
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
		$this->idTechnicalDevice = isset($data['id_technical_device']) ? $data['id_technical_device'] : null;
		$this->sort = isset($data['sort']) ? $data['sort'] : null;
		$this->type = isset($data['type']) ? $data['type'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_technical_device'] = $this->idTechnicalDevice;
		}
		$data['sort'] = $this->sort;
		$data['type'] = $this->type;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		
		return $data;
	}
	
}