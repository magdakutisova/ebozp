<?php
class My_Form_Element_Position extends Zend_Form_Element_Xhtml{
	
	public $helper = 'position';
	protected $_idPosition;
	protected $_name;
	protected $_note;
	protected $_private;
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator('ViewHelper');
		}
	}
	
	public function getIdPosition(){
		return $this->_idPosition;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getNote() {
		return $this->_note;
	}
	
	public function getPrivate(){
		return $this->_private;
	}
	
	public function setIdPosition($_idPosition){
		$this->_idPosition = $_idPosition;
	}
	
	public function setName($_name) {
		if (is_array($_name)){
			$this->_name = implode(' ', $_name);
		}
		else{
			$this->_name = $_name;
		}
	}
	
	public function setNote($_note) {
		if (is_array($_note)){
			$this->_note = implode(' ', $_note);
		}
		else{
			$this->_note = $_note;
		}
	}
	
	public function setPrivate($_private){
		$this->_private = $_private;
	}
	
	public function setValue($values){
		if(isset($values['id_position']) && isset($values['name']) && isset($values['note']) && isset($values['private'])){
			$this->setIdPosition($values['id_position']);
			$this->setName($values['name']);
			$this->setNote($values['note']);
			$this->setPrivate($values['private']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_position'] = $this->getIdPosition();
		$values['name'] = $this->getName();
		$values['note'] = $this->getNote();
		$values['private'] = $this->getPrivate();
		return $values;
	}
	
}