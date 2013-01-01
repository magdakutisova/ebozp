<?php
class My_Form_Element_Employee extends Zend_Form_Element_Xhtml{
	
	public $helper = 'employee';
	protected $_idEmployee;
	protected $_title1;
	protected $_firstName;
	protected $_surname;
	protected $_title2;
	protected $_manager;
	protected $_sex;
	protected $_yearOfBirth;
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator('ViewHelper')
			->addDecorator('ErrorsHtmlTag', array(
					'tag' => 'td',
					'colspan' => 7,
			));
		}
	}
	
	public function getIdEmployee(){
		return $this->_idEmployee;
	}
	
	public function getTitle1(){
		return $this->_title1;
	}
	
	public function getFirstName(){
		return $this->_firstName;
	}
	
	public function getSurname(){
		return $this->_surname;
	}
	
	public function getTitle2(){
		return $this->_title2;
	}
	
	public function getManager(){
		return $this->_manager;
	}
	
	public function getSex(){
		return $this->_sex;
	}
	
	public function getYearOfBirth(){
		return $this->_yearOfBirth;
	}
	
	public function setIdEmployee($_idEmployee){
		$this->_idEmployee = $_idEmployee;
	}
	
	public function setTitle1($_title1){
		$this->_title1 = $_title1;
	}
	
	public function setFirstName($_firstName){
		$this->_firstName = $_firstName;
	}
	
	public function setSurname($_surname){
		$this->_surname = $_surname;
	}
	
	public function setTitle2($_title2){
		$this->_title2 = $_title2;
	}
	
	public function setManager($_manager){
		$this->_manager = $_manager;
	}
	
	public function setSex($_sex){
		$this->_sex = $_sex;
	}
	
	public function setYearOfBirth($_yearOfBirth){
		$this->_yearOfBirth = $_yearOfBirth;
	}
	
	public function setValue($values){
		if(isset($values['id_employee']) && isset($values['title_1']) && isset($values['first_name']) && isset($values['surname'])
				&& isset($values['title_2']) && isset($values['manager']) && isset($values['sex']) && isset($values['year_of_birth'])){
			$this->setIdEmployee($values['id_employee']);
			$this->setTitle1($values['title_1']);
			$this->setFirstName($values['first_name']);
			$this->setSurname($values['surname']);
			$this->setTitle2($values['title_2']);
			$this->setManager($values['manager']);
			$this->setSex($values['sex']);
			$this->setYearOfBirth($values['year_of_birth']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_employee'] = $this->getIdEmployee();
		$values['title_1'] = $this->getTitle1();
		$values['first_name'] = $this->getFirstName();
		$values['surname'] = $this->getSurname();
		$values['title_2'] = $this->getTitle2();
		$values['manager'] = $this->getManager();
		$values['sex'] = $this->getSex();
		$values['year_of_birth'] = $this->getYearOfBirth();
		return $values;
	}
}