<?php
class My_Form_Element_Schooling extends Zend_Form_Element_Xhtml{
	
	public $helper = 'schooling';
	protected $_idSchooling;
	protected $_schooling;
	protected $_lastExecution;
	protected $_note;
	protected $_private;
	
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
	
	public function getIdSchooling(){
		return $this->_idSchooling;
	}
	
	public function getSchooling(){
		return $this->_schooling;
	}
	
	public function getLastExecution(){
		return $this->_lastExecution;
	}
	
	public function getNote(){
		return $this->_note;
	}
	
	public function getPrivate(){
		return $this->_private;
	}
	
	public function setIdSchooling($_idSchooling){
		$this->_idSchooling = $_idSchooling;
	}
	
	public function setSchooling($_schooling){
		$this->_schooling = $_schooling;
	}
	
	public function setLastExecution($_lastExecution){
		$this->_lastExecution = $_lastExecution;
	}
	
	public function setNote($_note){
		$this->_note = $_note;
	}
	
	public function setPrivate($_private){
		$this->_private = $_private;
	}
	
	public function setValue($values){
		if(isset($values['id_schooling']) && isset($values['schooling']) && isset($values['last_execution'])
				&& isset($values['note']) && isset($values['private'])){
			$this->setIdSchooling($values['id_schooling']);
			$this->setSchooling($values['schooling']);
			$this->setLastExecution($values['last_execution']);
			$this->setNote($values['note']);
			$this->setPrivate($values['private']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_schooling'] = $this->getIdSchooling();
		$values['schooling'] = $this->getSchooling();
		$values['last_execution'] = $this->getLastExecution();
		$values['note'] = $this->getNote();
		$values['private'] = $this->getPrivate();
		return $values;
	}
	
}