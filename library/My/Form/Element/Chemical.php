<?php
class My_Form_Element_Chemical extends Zend_Form_Element_Xhtml{
	
	public $helper = 'chemical';
	protected $_idChemical;
	protected $_chemical;
	protected $_newChemical;
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
	
	public function getNewChemical(){
		return $this->_newChemical;
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
	
	public function setNewChemical($_newChemical){
		$this->_newChemical = $_newChemical;
	}
	
	public function setExposition($_exposition){
		$this->_exposition = $_exposition;
	}
	
	public function setValue($values){
		if(isset($values['id_chemical']) && isset($values['chemical']) && isset($values['new_chemical'])
				&& isset($values['exposition'])){
			$this->setIdChemical($values['id_chemical']);
			$this->setChemical($values['chemical']);
			$this->setNewChemical($values['new_chemical']);
			$this->setExposition($values['exposition']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_chemical'] = $this->getIdChemical();
		$values['chemical'] = $this->getChemical();
		$values['new_chemical'] = $this->getNewChemical();
		$values['exposition'] = $this->getExposition();
		return $values;
	}
	
}