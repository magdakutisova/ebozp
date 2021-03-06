<?php
class My_Form_Element_EnvironmentFactorDetail extends Zend_Form_Element_Xhtml{
	
	public $helper = 'environmentFactorDetail';
	protected $_idEnvironmentFactor;
	protected $_factor;
	protected $_category;
	protected $_protectionMeasures;
	protected $_measurementTaken;
	protected $_source;
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
					'colspan' => 6,
			));
		}
	}
	
	public function getIdEnvironmentFactor(){
		return $this->_idEnvironmentFactor;
	}
	
	public function getFactor(){
		return $this->_factor;
	}
	
	public function getCategory(){
		return $this->_category;
	}
	
	public function getProtectionMeasures(){
		return $this->_protectionMeasures;
	}
	
	public function getMeasurementTaken(){
		return $this->_measurementTaken;
	}
	
	public function getSource(){
		return $this->_source;
	}
	
	public function getNote(){
		return $this->_note;
	}
	
	public function getPrivate(){
		return $this->_private;
	}
	
	public function setIdEnvironmentFactor($_idEnvironmentFactor){
		$this->_idEnvironmentFactor = $_idEnvironmentFactor;
	}
	
	public function setFactor($_factor){
		$this->_factor = $_factor;
	}
	
	public function setCategory($_category){
		$this->_category = $_category;
	}
	
	public function setProtectionMeasures($_protectionMeasures){
		$this->_protectionMeasures = $_protectionMeasures;
	}
	
	public function setMeasurementTaken($_measurementTaken){
		$this->_measurementTaken = $_measurementTaken;
	}
	
	public function setSource($_source){
		$this->_source = $_source;
	}
	
	public function setNote($_note){
		$this->_note = $_note;
	}
	
	public function setPrivate($_private){
		$this->_private = $_private;
	}
	
	public function setValue($values){
		$this->setIdEnvironmentFactor($values['id_environment_factor']);
		$this->setFactor($values['factor']);
		$this->setCategory($values['category']);
		$this->setProtectionMeasures($values['protection_measures']);
		$this->setMeasurementTaken($values['measurement_taken']);
		$this->setSource($values['source']);
		$this->setNote($values['note']);
		$this->setPrivate($values['private']);	
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_environment_factor'] = $this->getIdEnvironmentFactor();
		$values['factor'] = $this->getFactor();
		$values['category'] = $this->getCategory();
		$values['protection_measures'] = $this->getProtectionMeasures();
		$values['measurement_taken'] = $this->getMeasurementTaken();
		$values['source'] = $this->getSource();
		$values['note'] = $this->getNote();
		$values['private'] = $this->getPrivate();
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