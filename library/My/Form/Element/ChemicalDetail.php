<?php
class My_Form_Element_ChemicalDetail extends Zend_Form_Element_Xhtml{
	
	public $helper = 'chemicalDetail';
	protected $_idChemical;
	protected $_chemical; 
	protected $_usePurpose;
	protected $_usualAmount;
	
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
	
	public function getIdChemical(){
		return $this->_idChemical;
	}
	
	public function getChemical(){
		return $this->_chemical;
	}
	
	public function getUsePurpose(){
		return $this->_usePurpose;
	}
	
	public function getUsualAmount(){
		return $this->_usualAmount;
	}
		
	public function setIdChemical($_idChemical){
		$this->_idChemical = $_idChemical;
	}
	
	public function setChemical($_chemical){
		$this->_chemical = $_chemical;
	}
	
	public function setUsePurpose($_usePurpose){
		$this->_usePurpose = $_usePurpose;
	}
	
	public function setUsualAmount($_usualAmount){
		$this->_usualAmount = $_usualAmount;
	}
	
	public function setValue($values){
		$this->setIdChemical($values['id_chemical']);
		$this->setChemical($values['chemical']);
		$this->setUsePurpose($values['use_purpose']);
		$this->setUsualAmount($values['usual_amount']);
		return $this;
	}
	
	public function getValue(){		
		$values = array();
		$values['id_chemical'] = $this->getIdChemical();
		$values['chemical'] = $this->getChemical();
		$values['use_purpose'] = $this->getUsePurpose();
		$values['usual_amount'] = $this->getUsualAmount();
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