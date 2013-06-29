<?php
class My_Form_Element_Responsibility extends Zend_Form_Element_Xhtml{
	
	public $helper = 'responsibility';
	protected $_idResponsibility;
	protected $_idEmployee;
	
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
	
	public function getIdResponsibility(){
		return $this->_idResponsibility;
	}
	
	public function getIdEmployee(){
		return $this->_idEmployee;
	}
	
	public function setIdResponsibility($_idResponsibility){
		$this->_idResponsibility = $_idResponsibility;
	}
	
	public function setIdEmployee($_idEmployee){
		$this->_idEmployee = $_idEmployee;
	}
	
	public function setValue($values){
		$this->setIdResponsibility($values['id_responsibility']);
		$this->setIdEmployee($values['id_employee']);
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_responsibility'] = $this->getIdResponsibility();
		$values['id_employee'] = $this->getIdEmployee();
		return $values;
	}
	
}