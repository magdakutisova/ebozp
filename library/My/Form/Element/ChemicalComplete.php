<?php
class My_Form_Element_ChemicalComplete extends Zend_Form_Element_Xhtml{
	
	public $helper = 'chemicalComplete';
	protected $_idChemical;
	protected $_chemical;
	protected $_newChemical;
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
	
	public function getNewChemical(){
		return $this->_newChemical;
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
	
	public function setNewChemical($_newChemical){
		$this->_newChemical = $_newChemical;
	}
	
	public function setUsePurpose($_usePurpose){
		$this->_usePurpose = $_usePurpose;
	}
	
	public function setUsualAmount($_usualAmount){
		$this->_usualAmount = $_usualAmount;
	}
	
	public function setValue($values){
		if(isset($values['id_chemical']) && isset($values['chemical']) && isset($values['new_chemical']) && isset($values['use_purpose']) && isset($values['usual_amount'])){
			$this->setIdChemical($values['id_chemical']);
			$this->setChemical($values['chemical']);
			$this->setNewChemical($values['new_chemical']);
			$this->setUsePurpose($values['use_purpose']);
			$this->setUsualAmount($values['usual_amount']);
		}
		return $this;
	}
	
	public function getValue(){		
		$values = array();
		$values['id_chemical'] = $this->getIdChemical();
		$values['chemical'] = $this->getChemical();
		$values['new_chemical'] = $this->getNewChemical();
		$values['use_purpose'] = $this->getUsePurpose();
		$values['usual_amount'] = $this->getUsualAmount();
		return $values;
	}
	
}