<?php
class My_Form_Element_Chemical2Detail extends Zend_Form_Element_Xhtml{
	
	public $helper = 'chemical2Detail';
	protected $_idChemical;
	protected $_chemical;
	protected $_exposition;
	
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
	
	public function getExposition(){
		return $this->_exposition;
	}
	
	public function setIdChemical($_idChemical){
		$this->_idChemical = $_idChemical;
	}
	
	public function setChemical($_chemical){
		$this->_chemical = $_chemical;
	}
	
	public function setExposition($_exposition){
		$this->_exposition = $_exposition;
	}
	
	public function setValue($values){
		$this->setIdChemical($values['id_chemical']);
		$this->setChemical($values['chemical']);
		$this->setExposition($values['exposition']);
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_chemical'] = $this->getIdChemical();
		$values['chemical'] = $this->getChemical();
		$values['exposition'] = $this->getExposition();
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