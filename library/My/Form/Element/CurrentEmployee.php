<?php
class My_Form_Element_CurrentEmployee extends Zend_Form_Element_Xhtml{
	
	public $helper = 'currentEmployee';
	protected $_idEmployee;
	protected $_fullName;
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator('ViewHelper')
			->addDecorator('ErrorsHtmlTag', array(
					'tag' => 'td',
					'colspan' => 6,
			));
		}
	}
	
	public function getIdEmployee(){
		return $this->_idEmployee;
	}
	
	public function getFullName(){
		return $this->_fullName;
	}
	
	public function setIdEmployee($_idEmployee){
		$this->_idEmployee = $_idEmployee;
	}
	
	public function setFullName($_fullName){
		$this->_fullName = $_fullName;
	}
	
	public function setValue($values){
		if(isset($values['id_employee']) && isset($values['full_name'])){
			$this->setIdEmployee($values['id_employee']);
			$this->setFullName($values['full_name']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_employee'] = $this->getIdEmployee();
		$values['full_name'] = $this->getFullName();
		return $values;
	}
	
}