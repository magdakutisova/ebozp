<?php
class My_Form_Element_Doctor extends Zend_Form_Element_Xhtml{
	
	public $helper = 'doctor';
	protected $_idDoctor;
	protected $_names;
	protected $_phone;
	protected $_email;
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator('ViewHelper')
			->addDecorator('ErrorsHtmlTag', array(
					'tag' => 'td',
					'colspan' => 3,
			));
		}
	}
	
	public function getIdDoctor(){
		return $this->_idDoctor;
	}
	
	public function getNames(){
		return $this->_names;
	}
	
	public function getPhone(){
		return $this->_phone;
	}
	
	public function getEmail(){
		return $this->_email;
	}
	
	public function setIdDoctor($_idDoctor){
		$this->_idDoctor = $_idDoctor;
	}
	
	public function setNames($_names){
		$this->_names = $_names;
	}
	
	public function setPhone($_phone){
		$this->_phone = $_phone;
	}
	
	public function setEmail($_email){
		$this->_email = $_email;
	}
	
	public function setValue($values){
		$this->setIdDoctor($values['id_doctor']);
		$this->setNames($values['name']);
		$this->setPhone($values['phone']);
		$this->setEmail($values['email']);
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_doctor'] = $this->getIdDoctor();
		$values['name'] = $this->getNames();
		$values['phone'] = $this->getPhone();
		$values['email'] = $this->getEmail();
		return $values;
	}
	
	public function isValid($value){
		$filter = new My_Filter_CustomElementStrings();
		$value = $filter->filter($value);
		$this->setValue($value);
		$result = parent::isValid($value);
		return $result;
	}
	
}