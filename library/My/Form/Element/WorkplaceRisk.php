<?php
class My_Form_Element_WorkplaceRisk extends Zend_Form_Element_Xhtml{
	
	public $helper = 'workplaceRisk';
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
	
	public function getRisk() {
		return $this->_risk;
	}
	
	public function getNote() {
		return $this->_note;
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
		if(isset($values['risk']) && isset($values['note'])){
			$this->setRisk($values['risk']);
			$this->setNote($values['note']);
		}
		else{
			; //validace či co
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['risk'] = $this->getRisk();
		$values['note'] = $this->getNote();
		return $values;
	}
}