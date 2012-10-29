<?php
class My_Form_Element_Work extends Zend_Form_Element_Xhtml{
	
	public $helper = 'work';
	protected $_idWork;
	protected $_work;
	protected $_newWork;

	public function loadDefaultDecorators(){
		if($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if(empty($decorators)){
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
	
	public function setIdWork($_idWork){
		$this->_idWork = $_idWork;
	}
	
	public function setWork($_work){
		$this->_work = $_work;
	}
	
	public function setNewWork($_newWork){
		$this->_newWork = $_newWork;
	}
	
	public function setValue($values){
		if(isset($values['id_work']) && isset($values['work']) && isset($values['new_work'])){
			$this->setIdWork($values['id_work']);
			$this->setWork($values['work']);
			$this->setNewWork($values['new_work']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_work'] = $this->getIdWork();
		$values['work'] = $this->getWork();
		$values['new_work'] = $this->getNewWork();
		return $values;
	}
	
}