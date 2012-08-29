<?php
class My_Form_Element_WorkplaceFactor extends Zend_Form_Element_Xhtml{
	
	protected $_factor;
	protected $_applies;
	protected $_note;
	protected $_factorLabel = '';
	protected $_appliesLabel = 'Platí';
	protected $_noteLabel = 'Poznámka';
	protected $_factorId;
	protected $_appliesId;
	protected $_noteId;
	
	public function __construct($spec, $options = null){
		$this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		parent::__construct($spec, $options);
		$this->_factorId = 'factor_' . parent::getId();
		$this->_appliesId = 'applies_' . parent::getId();
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
		return 'WorkplaceFactor';
	}
	
	public function getLabel($type){
		if (isset($type)){
			switch ($type){
				case 'factor':
					return $this->getFactorLabel();
				case 'applies':
					return $this->getAppliesLabel();
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
				case 'factor':
					return $this->getFactorId();
				case 'applies':
					return $this->getAppliesId();
				case 'note':
					return $this->getNoteId();
			}
		}
		else{
			return parent::getId();
		}
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

	/**
	 * @return the $_factorLabel
	 */
	public function getFactorLabel() {
		return $this->_factorLabel;
	}

	/**
	 * @return the $_appliesLabel
	 */
	public function getAppliesLabel() {
		return $this->_appliesLabel;
	}

	/**
	 * @return the $_descriptionLabel
	 */
	public function getNoteLabel() {
		return $this->_noteLabel;
	}

	/**
	 * @return the $_factorId
	 */
	public function getFactorId() {
		return $this->_factorId;
	}

	/**
	 * @return the $_appliesId
	 */
	public function getAppliesId() {
		return $this->_appliesId;
	}

	/**
	 * @return the $_descriptionId
	 */
	public function getNoteId() {
		return $this->_noteId;
	}

	/**
	 * @param $_factor the $_factor to set
	 */
	public function setFactor($_factor) {
		$this->_factor = $_factor;
	}
	
	public function setFactorLabel($_factorLabel){
		$this->_factorLabel = $_factorLabel;
	}

	/**
	 * @param $_applies the $_applies to set
	 */
	public function setApplies($_applies) {
		$this->_applies = $_applies;
	}

	/**
	 * @param $_description the $_description to set
	 */
	public function setNote($_note) {
		$this->_note = $_note;
	}

	public function setValue($values){
		if(isset($values['factor']) && isset($values['applies']) && isset($values['note'])){
			$this->setFactor($values['factor']);
			$this->setApplies($values['applies']);
			$this->setNote($values['note']);
		}
		else{
			; //validace či co
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['factor'] = $this->getFactor();
		$values['applies'] = $this->getApplies();
		$values['note'] = $this->getNote();
		return $values;
	}
	
}