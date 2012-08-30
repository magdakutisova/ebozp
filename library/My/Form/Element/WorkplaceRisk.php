<?php
class My_Form_Element_WorkplaceRisk extends Zend_Form_Element_Xhtml{
	
	protected $_risk;
	protected $_note;
	protected $_riskLabel = 'Riziko';
	protected $_noteLabel = 'Popis, poznámka';
	protected $_riskId;
	protected $_noteId;
	
	public function __construct($spec, $options = null){
		$this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		parent::__construct($spec, $options);
		$this->_riskId = 'risk_' . parent::getId();
		$this->_noteId = 'note_' . parent::getId();
	}
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator($this->getDecoratorName())
				->addDecorator('Errors')
				->addDecorator('Description', array(
					'tag' => 'p',
					'class' => 'description')
				)
				->addDecorator('HtmlTag', array(
					'tag' => 'tr',
					'id' => $this->getName())
				);
		}
	}
	
	public function getDecoratorName(){
		return 'WorkplaceRisk';
	}
	
	public function getLabel($type){
		if (isset($type)){
			switch ($type){
				case 'risk':
					return $this->getRiskLabel();
				case 'note':
					return $this->getNoteLabel();
			}
		}
		else{
			return parent::getId();
		}
	}
	
	public function getId($type){
		if(isset($type)){
			switch ($type){
				case 'risk':
					return $this->getRiskId();
				case 'note':
					return $this->getNoteId();
			}
		}
		else{
			return parent::getId();
		}
	}
	
	public function getRisk() {
		return $this->_risk;
	}
	
	public function getNote() {
		return $this->_note;
	}
	
	public function getRiskLabel() {
		return $this->_riskLabel;
	}
	
	public function getNoteLabel() {
		return $this->_noteLabel;
	}
	
	public function getRiskId() {
		return $this->_riskId;
	}
	
	public function getNoteId() {
		return $this->_noteId;
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