<?php
class My_Form_Element_TechnicalDevice extends Zend_Form_Element_Xhtml{
	
	public $helper = 'technicalDevice';
	protected $_idTechnicalDevice;
	protected $_sort;
	protected $_newSort;
	protected $_type;
	protected $_newType;
	
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
	
	public function getIdTechnicalDevice(){
		return $this->_idTechnicalDevice;
	}
	
	public function getSort(){
		return $this->_sort;
	}
	
	public function getNewSort(){
		return $this->_newSort;
	}
	
	public function getType(){
		return $this->_type;
	}
	
	public function getNewType(){
		return $this->_newType;
	}
	
	public function setIdTechnicalDevice($_idTechnicalDevice){
		$this->_idTechnicalDevice = $_idTechnicalDevice;
	}
	
	public function setSort($_sort){
		$this->_sort = $_sort;
	}
	
	public function setNewSort($_newSort){
		$this->_newSort = $_newSort;
	}
	
	public function setType($_type){
		$this->_type = $_type;
	}
	
	public function setNewType($_newType){
		$this->_newType = $_newType;
	}
	
	public function setValue($values){
		if(isset($values['id_technical_device']) && isset($values['sort']) && isset($values['new_sort']) && isset($values['type']) && isset($values['new_type'])){
			$this->setIdTechnicalDevice($values['id_technical_device']);
			$this->setSort($values['sort']);
			$this->setNewSort($values['new_sort']);
			$this->setType($values['type']);
			$this->setNewType($values['new_type']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_technical_device'] = $this->getIdTechnicalDevice();
		$values['sort'] = $this->getSort();
		$values['new_sort'] = $this->getNewSort();
		$values['type'] = $this->getType();
		$values['new_type'] = $this->getNewType();		
		return $values;
	}
	
}