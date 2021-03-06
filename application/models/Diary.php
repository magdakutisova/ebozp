<?php
class Application_Model_Diary{
	
	private $idDiary;
	private $date;
	private $message;
	private $subsidiaryId;
	private $author;
	private $subsidiaryName;
	private $subsidiaryTown;
	private $subsidiaryStreet;
	private $subsidiaryCode;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idDiary
	 */
	public function getIdDiary() {
		return $this->idDiary;
	}

	/**
	 * @param $idDiary the $idDiary to set
	 */
	public function setIdDiary($idDiary) {
		$this->idDiary = $idDiary;
	}

	/**
	 * @return the $date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @param $date the $date to set
	 */
	public function setDate($date) {
		$this->date = $date;
	}

	/**
	 * @return the $message
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * @param $message the $message to set
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * @return the $subsidiaryId
	 */
	public function getSubsidiaryId() {
		return $this->subsidiaryId;
	}

	/**
	 * @param $subsidiaryId the $subsidiaryId to set
	 */
	public function setSubsidiaryId($subsidiaryId) {
		$this->subsidiaryId = $subsidiaryId;
	}

	public function getSubsidiaryName() {
		return $this->subsidiaryName;
	}

	public function getSubsidiaryTown() {
		return $this->subsidiaryTown;
	}

	public function getSubsidiaryStreet() {
		return $this->subsidiaryStreet;
	}

	public function getSubsidiaryCode() {
		return $this->subsidiaryCode;
	}

	/**
	 * @return the $author
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @param $author the $author to set
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	public function populate(array $data){
		$this->idDiary = isSet($data['id_diary']) ? $data['id_diary'] : null;
		$this->date = isSet($data['date']) ? $data['date'] : null;
		$this->message = isSet($data['message']) ? $data['message'] : null;
		$this->subsidiaryId = isSet($data['subsidiary_id']) ? $data['subsidiary_id'] : null;
		$this->author = isSet($data['author']) ? $data['author'] : null; 
		$this->subsidiaryName = isSet($data['subsidiary_name']) ? $data['subsidiary_name'] : null; 
		$this->subsidiaryTown = isSet($data['subsidiary_town']) ? $data['subsidiary_town'] : null; 
		$this->subsidiaryStreet = isSet($data['subsidiary_street']) ? $data['subsidiary_street'] : null; 
		$this->subsidiaryCode = isSet($data['subsidiary_code']) ? $data['subsidiary_code'] : null; 
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if (!$toUpdate){
			$data['id_diary'] = $this->idDiary;
		}
		$data['date'] = $this->date;
		$data['message'] = $this->message;
		$data['subsidiary_id'] = $this->subsidiaryId;
		$data['author'] = $this->author;
		
		return $data;
	}
	
}