<?php
class Application_Model_Subsidiary{
	
	private $idSubsidiary;
	private $subsidiaryName;
	private $subsidiaryStreet;
	private $subsidiaryCode;
	private $subsidiaryTown;
	private $contactPerson;
	private $phone;
	private $email;
	private $supervisionFrequency;
	private $doctor;
	private $clientId;
	private $private;
	private $hq;
	private $deleted;
	
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
	 * @return the $contactPerson
	 */
	public function getContactPerson() {
		return $this->contactPerson;
	}

	/**
	 * @param $contactPerson the $contactPerson to set
	 */
	public function setContactPerson($contactPerson) {
		$this->contactPerson = $contactPerson;
	}

	/**
	 * @return the $phone
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * @param $phone the $phone to set
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
	}

	/**
	 * @return the $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param $email the $email to set
	 */
	public function setEmail($email) {
		$this->email = $email;
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
	 * @return the $doctor
	 */
	public function getDoctor() {
		return $this->doctor;
	}

	/**
	 * @param $doctor the $doctor to set
	 */
	public function setDoctor($doctor) {
		$this->doctor = $doctor;
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

	/**
	 * @param $deleted the $deleted to set
	 */
	public function setDeleted($deleted) {
		$this->deleted = $deleted;
	}

	public function populate(array $data){
		$this->idSubsidiary = isset($data['id_subsidiary']) ? $data['id_subsidiary'] : null;
		$this->subsidiaryName = isset($data['subsidiary_name']) ? $data['subsidiary_name'] : null;
		$this->subsidiaryStreet = isset($data['subsidiary_street']) ? $data['subsidiary_street'] : null;
		$this->subsidiaryCode = isset($data['subsidiary_code']) ? $data['subsidiary_code'] : null;
		$this->subsidiaryTown = isset($data['subsidiary_town']) ? $data['subsidiary_town'] : null;
		$this->contactPerson = isset($data['contact_person']) ? $data['contact_person'] : null;
		$this->phone = isset($data['phone']) ? $data['phone'] : null;
		$this->email = isset($data['email']) ? $data['email'] : null;
		$this->supervisionFrequency = isset($data['supervision_frequency']) ? $data['supervision_frequency'] : null;
		$this->doctor = isset($data['doctor']) ? $data['doctor'] : null;
		$this->clientId = isset($data['client_id']) ? $data['client_id'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		$this->hq = isset($data['hq']) ? $data['hq'] : null;
		//$this->deleted = isset($data['deleted']) ? $data['deleted'] : null;
		
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
		$data['contact_person'] = $this->contactPerson;
		$data['phone'] = $this->phone;
		$data['email'] = $this->email;
		$data['supervision_frequency'] = $this->supervisionFrequency;
		$data['doctor'] = $this->doctor;
		$data['client_id'] = $this->clientId;
		$data['private'] = $this->private;
		$data['hq'] = $this->hq;
		//$data['deleted'] = $this->deleted;
		
		return $data;
	}
	
}