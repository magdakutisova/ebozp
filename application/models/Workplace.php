<?php
class Application_Model_Workplace{
	
	private $idWorkplace;
	private $name;
	private $businessHours;
	private $description;
	private $risks;
	private $riskNote;
	private $riskPrivate;
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
	
	public function getBusinessHours(){
		return $this->businessHours;
	}

	/**
	 * @return the $description
	 */
	public function getDescription() {
		return $this->description;
	}
	
	public function getRisks(){
		return $this->risks;
	}
	
	public function getRiskNote(){
		return $this->riskNote;
	}
	
	public function getRiskPrivate(){
		return $this->riskPrivate;
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
	
	public function setBusinessHours($businessHours){
		$this->businessHours = $businessHours;
	}

	/**
	 * @param $description the $description to set
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function setRisks($risks){
		$this->risks = $risks;
	}
	
	public function setRiskNote($riskNote){
		$this->riskNote = $riskNote;
	}
	
	public function setRiskPrivate($riskPrivate){
		$this->riskPrivate = $riskPrivate;
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
		$this->businessHours = isset($data['business_hours']) ? $data['business_hours'] : null; 
		$this->description = isset($data['description']) ? $data['description'] : null;
		$this->risks = isset($data['risks']) ? $data['risks'] : null;
		$this->riskNote = isset($data['risk_note']) ? $data['risk_note'] : null;
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
		$data['business_hours'] = $this->businessHours;
		$data['description'] = $this->description;
		$data['risks'] = $this->risks;
		$data['risk_note'] = $this->riskNote;
		$data['subsidiary_id'] = $this->subsidiaryId;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		
		return $data;
	}
	
}