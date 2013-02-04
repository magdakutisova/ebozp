<?php
class My_Form_Element_WorkComplete extends Zend_Form_Element_Xhtml{
	
	public $helper = 'workComplete';
	protected $_idWork;
	protected $_work;
	protected $_newWork;
	protected $_frequency;
	protected $_newFrequency;
	
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
	
	public function getIdWork(){
		return $this->_idWork;
	}
	
	public function getWork(){
		return $this->_work;
	}
	
	public function getNewWork(){
		return $this->_newWork;
	}
	
	public function getFrequency(){
		return $this->_frequency;
	}
	
	public function getNewFrequency(){
		return $this->_newFrequency;
	}
	
	public function setIdWork($_idWork){
		$this->_idWork = $_idWork;
	}
	
	public function setWork($_work){
		$this->_work = $_work;
	}
	
	public function setNewWork($_newWork){
		$this->_newWork = $_newWork;
	}
	
	public function setFrequency($_frequency){
		$this->_frequency = $_frequency;
	}
	
	public function setNewFrequency($_newFrequency){
		$this->_newFrequency = $_newFrequency;
	}
	
	public function setValue($values){
		if(isset($values['id_work']) && isset($values['work']) && isset($values['new_work'])
				&& isset($values['frequency']) && isset($values['new_frequency'])){
			$this->setIdWork($values['id_work']);
			$this->setWork($values['work']);
			$this->setNewWork($values['new_work']);
			$this->setFrequency($values['frequency']);
			$this->setNewFrequency($values['new_frequency']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_work'] = $this->getIdWork();
		$values['work'] = $this->getWork();
		$values['new_work'] = $this->getNewWork();
		$values['frequency'] = $this->getFrequency();
		$values['new_frequency'] = $this->getNewFrequency();
		return $values;
	}
	
}