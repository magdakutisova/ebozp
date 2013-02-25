<?php
class Application_Model_Employee{
	
	private $idEmployee;
	private $firstName;
	private $surname;
	private $title1;
	private $title2;
	private $yearOfBirth;
	private $sex;
	private $manager;
	private $workNumber;
	private $email;
	private $phone;
	private $note;
	private $private;
	private $positionId;
	private $clientId;
	
	public function __construct($options = array()){
		if(!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idEmployee
	 */
	public function getIdEmployee() {
		return $this->idEmployee;
	}

	/**
	 * @return the $firstName
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @return the $surname
	 */
	public function getSurname() {
		return $this->surname;
	}

	/**
	 * @return the $title1
	 */
	public function getTitle1() {
		return $this->title1;
	}

	/**
	 * @return the $title2
	 */
	public function getTitle2() {
		return $this->title2;
	}

	/**
	 * @return the $yearOfBirth
	 */
	public function getYearOfBirth() {
		return $this->yearOfBirth;
	}

	/**
	 * @return the $sex
	 */
	public function getSex() {
		return $this->sex;
	}

	/**
	 * @return the $manager
	 */
	public function getManager() {
		return $this->manager;
	}

	/**
	 * @return the $workNumber
	 */
	public function getWorkNumber() {
		return $this->workNumber;
	}

	/**
	 * @return the $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return the $phone
	 */
	public function getPhone() {
		return $this->phone;
	}
	
	public function getNote(){
		return $this->note;
	}

	/**
	 * @return the $private
	 */
	public function getPrivate() {
		return $this->private;
	}

	public function getPositionId(){
		return $this->positionId;
	}
	
	public function getClientId(){
		return $this->clientId;
	}
	
	/**
	 * @param $idEmployee the $idEmployee to set
	 */
	public function setIdEmployee($idEmployee) {
		$this->idEmployee = $idEmployee;
	}

	/**
	 * @param $firstName the $firstName to set
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
	}

	/**
	 * @param $surname the $surname to set
	 */
	public function setSurname($surname) {
		$this->surname = $surname;
	}

	/**
	 * @param $title1 the $title1 to set
	 */
	public function setTitle1($title1) {
		$this->title1 = $title1;
	}

	/**
	 * @param $title2 the $title2 to set
	 */
	public function setTitle2($title2) {
		$this->title2 = $title2;
	}

	/**
	 * @param $yearOfBirth the $yearOfBirth to set
	 */
	public function setYearOfBirth($yearOfBirth) {
		$this->yearOfBirth = $yearOfBirth;
	}

	/**
	 * @param $sex the $sex to set
	 */
	public function setSex($sex) {
		$this->sex = $sex;
	}

	/**
	 * @param $manager the $manager to set
	 */
	public function setManager($manager) {
		$this->manager = $manager;
	}

	/**
	 * @param $workNumber the $workNumber to set
	 */
	public function setWorkNumber($workNumber) {
		$this->workNumber = $workNumber;
	}

	/**
	 * @param $email the $email to set
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @param $phone the $phone to set
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
	}
	
	public function setNote($note){
		$this->note = $note;
	}

	/**
	 * @param $private the $private to set
	 */
	public function setPrivate($private) {
		$this->private = $private;
	}
	
	public function setPositionId($positionId){
		$this->positionId = $positionId;
	}
	
	public function setClientId($clientId){
		$this->clientId = $clientId;
	}

	public function populate(array $data){
		$this->idEmployee = isset($data['id_employee']) ? $data['id_employee'] : null;
		$this->firstName = isset($data['first_name']) ? $data['first_name'] : null;
		$this->surname = isset($data['surname']) ? $data['surname'] : null;
		$this->title1 = isset($data['title_1']) ? $data['title_1'] : null;
		$this->title2 = isset($data['title_2']) ? $data['title_2'] : null;
		$this->yearOfBirth = isset($data['year_of_birth']) ? $data['year_of_birth'] : null;
		$this->sex = isset($data['sex']) ? $data['sex'] : null;
		$this->manager = isset($data['manager']) ? $data['manager'] : null;
		$this->workNumber = isset($data['work_number']) ? $data['work_number'] : null;
		$this->email = isset($data['email']) ? $data['email'] : null;
		$this->phone = isset($data['phone']) ? $data['phone'] : null;
		$this->note = isset($data['note']) ? $data['note'] :  null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		$this->positionId = isset($data['position_id']) ? $data['position_id'] : null;
		$this->clientId = isset($data['client_id']) ? $data['client_id'] : null;

		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_employee'] = $this->idEmployee;
		}
		$data['first_name'] = $this->firstName;
		$data['surname'] = $this->surname;
		$data['title_1'] = $this->title1;
		$data['title_2'] = $this->title2;
		$data['year_of_birth'] = $this->yearOfBirth;
		$data['sex'] = $this->sex;
		$data['manager'] = $this->manager;
		$data['work_number'] = $this->workNumber;
		$data['email'] = $this->email;
		$data['phone'] = $this->phone;
		$data['note'] = $this->note;
		$data['private'] = $this->private;
		$data['position_id'] = $this->positionId;
		$data['client_id'] = $this->clientId;
		
		return $data;
	}
	
}