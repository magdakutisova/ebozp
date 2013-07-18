<?php
class Application_Model_Subsidiary implements Zend_Acl_Resource_Interface, Application_Model_UserOwnedInterface{
	
	private $idSubsidiary;
	private $subsidiaryName;
	private $subsidiaryStreet;
	private $subsidiaryCode;
	private $subsidiaryTown;
	private $supervisionFrequency;
	private $clientId;
	private $private;
	private $hq;
	private $difficulty;
	private $district;
	private $allowed;
	private $insuranceCompany;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}	
	
	/**
	 * @return the $idSubsidiary
	 */
	public function getIdSubsidiary() {
		return $this->idSubsidiary;
	}

	/**
	 * @param $idSubsidiary the $idSubsidiary to set
	 */
	public function setIdSubsidiary($idSubsidiary) {
		$this->idSubsidiary = $idSubsidiary;
	}

	/**
	 * @return the $subsidiaryName
	 */
	public function getSubsidiaryName() {
		return $this->subsidiaryName;
	}

	/**
	 * @param $subsidiaryName the $subsidiaryName to set
	 */
	public function setSubsidiaryName($subsidiaryName) {
		$this->subsidiaryName = $subsidiaryName;
	}

	/**
	 * @return the $subsidiaryStreet
	 */
	public function getSubsidiaryStreet() {
		return $this->subsidiaryStreet;
	}

	/**
	 * @param $subsidiaryStreet the $subsidiaryStreet to set
	 */
	public function setSubsidiaryStreet($subsidiaryStreet) {
		$this->subsidiaryStreet = $subsidiaryStreet;
	}

	/**
	 * @return the $subsidiaryCode
	 */
	public function getSubsidiaryCode() {
		return $this->subsidiaryCode;
	}

	/**
	 * @param $subsidiaryCode the $subsidiaryCode to set
	 */
	public function setSubsidiaryCode($subsidiaryCode) {
		$this->subsidiaryCode = $subsidiaryCode;
	}

	/**
	 * @return the $subsidiaryTown
	 */
	public function getSubsidiaryTown() {
		return $this->subsidiaryTown;
	}

	/**
	 * @param $subsidiaryTown the $subsidiaryTown to set
	 */
	public function setSubsidiaryTown($subsidiaryTown) {
		$this->subsidiaryTown = $subsidiaryTown;
	}

	/**
	 * @return the $supervisionFrequency
	 */
	public function getSupervisionFrequency() {
		return $this->supervisionFrequency;
	}

	/**
	 * @param $supervisionFrequency the $supervisionFrequency to set
	 */
	public function setSupervisionFrequency($supervisionFrequency) {
		$this->supervisionFrequency = $supervisionFrequency;
	}

	/**
	 * @return the $clientId
	 */
	public function getClientId() {
		return $this->clientId;
	}

	/**
	 * @param $clientId the $clientId to set
	 */
	public function setClientId($clientId) {
		$this->clientId = $clientId;
	}

	/**
	 * @return the $private
	 */
	public function getPrivate() {
		return $this->private;
	}

	/**
	 * @param $private the $private to set
	 */
	public function setPrivate($private) {
		$this->private = $private;
	}

	/**
	 * @return the $hq
	 */
	public function getHq() {
		return $this->hq;
	}

	/**
	 * @param $hq the $hq to set
	 */
	public function setHq($hq) {
		$this->hq = $hq;
	}

	/**
	 * @return the $deleted
	 */
	public function getDeleted() {
		return $this->deleted;
	}
	
	public function getDifficulty(){
		return $this->difficulty;
	}
	
	public function setDifficulty($difficulty){
		$this->difficulty = $difficulty;
	}
	
	public function getDistrict(){
		return $this->district;
	}
	
	public function setDistrict($district){
		$this->district = $district;
	}

	/**
	 * @return the $insuranceCompany
	 */
	public function getInsuranceCompany() {
		return $this->insuranceCompany;
	}
	
	/**
	 * @param $insuranceCompany the $insuranceCompany to set
	 */
	public function setInsuranceCompany($insuranceCompany) {
		$this->insuranceCompany = $insuranceCompany;
	}
	
	/**
	 * @return the $allowed
	 */
	public function getAllowed() {
		return $this->allowed;
	}

	/**
	 * @param $allowed the $allowed to set
	 */
	public function setAllowed($allowed) {
		$this->allowed = $allowed;
	}

	public function populate(array $data){
		$this->idSubsidiary = isset($data['id_subsidiary']) ? $data['id_subsidiary'] : null;
		$this->subsidiaryName = isset($data['subsidiary_name']) ? $data['subsidiary_name'] : null;
		$this->subsidiaryStreet = isset($data['subsidiary_street']) ? $data['subsidiary_street'] : null;
		$this->subsidiaryCode = isset($data['subsidiary_code']) ? $data['subsidiary_code'] : null;
		$this->subsidiaryTown = isset($data['subsidiary_town']) ? $data['subsidiary_town'] : null;
		$this->supervisionFrequency = isset($data['supervision_frequency']) ? $data['supervision_frequency'] : null;
		$this->clientId = isset($data['client_id']) ? $data['client_id'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		$this->hq = isset($data['hq']) ? $data['hq'] : null;
		$this->difficulty = isset($data['difficulty']) ? $data['difficulty'] : null;
		$this->district = isset($data['district']) ? $data['district'] : null;
		$this->insuranceCompany = isset($data['insurance_company']) ? $data['insurance_company'] : null;
				
		return $this;
	}
	
	public function toArray($toDelete = false){
		$data = array();
		if(!$toDelete){
			$data['id_subsidiary'] = $this->idSubsidiary;
		}
		$data['subsidiary_name'] = $this->subsidiaryName;
		$data['subsidiary_street'] = $this->subsidiaryStreet;
		$data['subsidiary_code'] = $this->subsidiaryCode;
		$data['subsidiary_town'] = $this->subsidiaryTown;
		$data['supervision_frequency'] = $this->supervisionFrequency;
		$data['client_id'] = $this->clientId;
		$data['private'] = $this->private;
		$data['hq'] = $this->hq;
		$data['difficulty'] = $this->difficulty;
		$data['district'] = $this->district;
		$data['insurance_company'] = $this->insuranceCompany;
		
		return $data;
	}
/**
	 * 
	 */
	public function getResourceId() {
		return 'subs';
	}
/**
	 * @param Application_Model_User $user
	 */
	public function isOwnedByUser(Application_Model_User $user) {
		return $user->hasSubsidiary($this);
	}


	
}