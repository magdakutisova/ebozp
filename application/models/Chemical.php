<?php
class Application_Model_Chemical{
	
	private $idChemical;
	private $chemical;
	private $usePurpose;
	private $usualAmount;
	private $note;
	private $private;
	
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
	 * @return the $usePurpose
	 */
	public function getUsePurpose() {
		return $this->usePurpose;
	}

	/**
	 * @return the $usualAmount
	 */
	public function getUsualAmount() {
		return $this->usualAmount;
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

	/**
	 * @param $usePurpose the $usePurpose to set
	 */
	public function setUsePurpose($usePurpose) {
		$this->usePurpose = $usePurpose;
	}

	/**
	 * @param $usualAmount the $usualAmount to set
	 */
	public function setUsualAmount($usualAmount) {
		$this->usualAmount = $usualAmount;
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
		$this->idChemical = isset($data['id_chemical']) ? $data['id_chemical'] : null;
		$this->chemical = isset($data['chemical']) ? $data['chemical'] : null;
		$this->usePurpose = isset($data['use_purpose']) ? $data['use_purpose'] : null;
		$this->usualAmount = isset($data['usual_amount']) ? $data['usual_amount'] : null;
		$this->note = isset($data['note']) ? $data['note'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;

		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_chemical'] = $this->idChemical;
		}
		$data['chemical'] = $this->chemical;
		$data['use_purpose'] = $this->usePurpose;
		$data['usual_amount'] = $this->usualAmount;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		
		return $data;
	}
	
}