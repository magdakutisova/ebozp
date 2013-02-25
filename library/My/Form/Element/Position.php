<?php
class My_Form_Element_Position extends Zend_Form_Element_Xhtml{
	
	public $helper = 'position';
	protected $_idPosition;
	protected $_position;
	protected $_newPosition;
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator('ViewHelper')
			->addDecorator('ErrorsHtmlTag', array(
					'tag' => 'td',
					'colspan' => 5,
				));
		}
	}
	
	public function getIdPosition(){
		return $this->_idPosition;
	}
	
	public function getPosition() {
		return $this->_position;
	}
	
	public function getNewPosition(){
		return $this->_newPosition;
	}
		
	public function setIdPosition($_idPosition){
		$this->_idPosition = $_idPosition;
	}
	
	public function setPosition($_position) {
		if (is_array($_position)){
			$this->_position = implode(' ', $_position);
		}
		else{
			$this->_position = $_position;
		}
	}
	
	public function setNewPosition($_newPosition){
		$this->_newPosition = $_newPosition;
	}
		
	public function setValue($values){
		$this->setIdPosition($values['id_position']);
		$this->setPosition($values['position']);
		$this->setNewPosition($values['new_position']);
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_position'] = $this->getIdPosition();
		$values['position'] = $this->getPosition();
		$values['new_position'] = $this->getNewPosition();
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