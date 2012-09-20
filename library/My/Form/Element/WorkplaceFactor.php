<?php
class My_Form_Element_WorkplaceFactor extends Zend_Form_Element_Xhtml{
	
	public $helper = 'workplaceFactor';
	protected $_idWorkplaceFactor;
	protected $_factor;
	protected $_applies;
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
	
	public function getIdWorkplaceFactor(){
		return $this->_idWorkplaceFactor;
	}
	
	/**
	 * @return the $_factor
	 */
	public function getFactor() {
		return $this->_factor;
	}

	/**
	 * @return the $_applies
	 */
	public function getApplies() {
		return $this->_applies;
	}

	/**
	 * @return the $_description
	 */
	public function getNote() {
		return $this->_note;
	}

	public function setIdWorkplaceFactor($_idWorkplaceFactor){
		$this->_idWorkplaceFactor = $_idWorkplaceFactor;
	}
	
	/**
	 * @param $_factor the $_factor to set
	 */
	public function setFactor($_factor) {
		if (is_array($_factor)){
			$this->_factor = implode(' ', $_factor);
		}
		else{
			$this->_factor = $_factor;
		}
	}

	/**
	 * @param $_applies the $_applies to set
	 */
	public function setApplies($_applies) {
		if(is_array($_applies)){
			$this->_applies = implode(' ', $_applies);
		}
		else{
			$this->_applies = $_applies;
		}
	}

	/**
	 * @param $_description the $_description to set
	 */
	public function setNote($_note) {
		if(is_array($_note)){
			$this->_note = implode(' ', $_note);
		}
		else{
			$this->_note = $_note;
		}
	}

	public function setValue($values){
		if(isset($values['id_workplace_factor']) && isset($values['factor']) && isset($values['applies']) && isset($values['note'])){
			$this->setIdWorkplaceFactor($values['id_workplace_factor']);
			$this->setFactor($values['factor']);
			$this->setApplies($values['applies']);
			$this->setNote($values['note']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['id_workplace_factor'] = $this->getIdWorkplaceFactor();
		$values['factor'] = $this->getFactor();
		$values['applies'] = $this->getApplies();
		$values['note'] = $this->getNote();
		
		return $values;
	}
	
}