<?php
class My_Form_Element_Workplace extends Zend_Form_Element_Xhtml{
	
	public $helper = 'workplace';
	protected $_workplaces;
	protected $_newWorkplaces;
	
	public function loadDefaultDecorators(){
		if ($this->loadDefaultDecoratorsIsDisabled()){
			return;
		}
		$decorators = $this->getDecorators();
		if (empty($decorators)){
			$this->addDecorator('ViewHelper')
			->addDecorator('ErrorsHtmlTag', array(
					'tag' => 'td',
					'colspan' => 7,
			));
		}
	}
	
	public function getWorkplaces(){
		return $this->_workplaces;
	}
	
	public function getNewWorkplaces(){
		return $this->_newWorkplaces;
	}
	
	public function setWorkplaces($_workplaces){
		$this->_workplaces = $_workplaces;
	}
	
	public function setNewWorkplaces($_newWorkplaces){
		$this->_newWorkplaces = $_newWorkplaces;
	}
	
	public function setValue($values){
		if(isset($values['workplaces'])	&& isset($values['new_workplaces'])){
			$this->setWorkplaces($values['workplaces']);
			$this->setNewWorkplaces($values['new_workplaces']);
		}
		return $this;
	}
	
	public function getValue(){
		$values = array();
		$values['workplaces'] = $this->getWorkplaces();
		$values['new_workplaces'] = $this->getNewWorkplaces();
		return $values;
	}
	
}