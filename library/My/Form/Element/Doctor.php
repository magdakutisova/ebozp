<?php
class My_Form_Element_Doctor extends Zend_Form_Element_Xhtml{
	
	public $helper = 'doctor';
	protected $_idDoctor;
	protected $_names;
	protected $_street;
	protected $_town;
	
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
	
	public function getStreet(){
		return $this->_street;
	}
	
	public function getTown(){
		return $this->_town;
	}
	
	public function setIdDoctor($_idDoctor){
		$this->_idDoctor = $_idDoctor;
	}
	
	public function setNames($_names){
		$this->_names = $_names;
	}
	
	public function setStreet($_street){
		$this->_street = $_street;
	}
	
	public function setTown($_town){
		$this->_town = $_town;
	}
	
	public function setValue($values){
		$this->setIdDoctor($values['id_doctor']);
		$this->setNames($values['name']);
		$this->setStreet($values['street']);
		$this->setTown($values['town']);
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_doctor'] = $this->getIdDoctor();
		$values['name'] = $this->getNames();
		$values['street'] = $this->getStreet();
		$values['town'] = $this->getTown();
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