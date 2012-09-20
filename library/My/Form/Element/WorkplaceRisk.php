<?php
class My_Form_Element_WorkplaceRisk extends Zend_Form_Element_Xhtml{
	
	public $helper = 'workplaceRisk';
	protected $_idWorkplaceRisk;
	protected $_risk;
	protected $_note;
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator('ViewHelper')
				->addDecorator('ErrorsHtmlTag', array(
					'tag' => 'td',
				))
				->addDecorator('HtmlTag', array(
					'tag' => 'tr',
					'id' => $this->getName())
				)
				->addDecorator('HtmlTag', array(
					'tag' => 'tr',
				));
		}
	}
	
	public function getIdWorkplaceRisk(){
		return $this->_idWorkplaceRisk;
	}
	
	public function getRisk() {
		return $this->_risk;
	}
	
	public function getNote() {
		return $this->_note;
	}
	
	public function setIdWorkplaceRisk($_idWorkplaceRisk){
		$this->_idWorkplaceRisk = $_idWorkplaceRisk;
	}
	
	public function setRisk($_risk) {
		if (is_array($_risk)){
			$this->_risk = implode(' ', $_risk);
		}
		else{
			$this->_risk = $_risk;
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
	
	public function setValue($values){
		if(isset($values['id_workplace_risk']) && isset($values['risk']) && isset($values['note'])){
			$this->setIdWorkplaceRisk($values['id_workplace_risk']);
			$this->setRisk($values['risk']);
			$this->setNote($values['note']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_workplace_risk'] = $this->getIdWorkplaceRisk();
		$values['risk'] = $this->getRisk();
		$values['note'] = $this->getNote();
		return $values;
	}
}