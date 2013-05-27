<?php
class Document_Form_Directory extends Zend_Form {
	
	public function init() {
		
		$this->setElementsBelongTo("directory");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'div')),
				'Form',
		));
		
		$decorators = array("ViewHelper", array("Label"), array("Errors"));
		$this->addElement("text", "name", array("required" => true, "label" => "Název adresáře : ", "decorators" => $decorators));
		
		$this->addElement("hidden", "parent_id", array("decorators" => $decorators));
		$this->addElement("submit", "submit", array("label" => "Uložit", "decorators" => array("ViewHelper")));
	}
	
	public function setSubmit($name) {
		$this->_elements["submit"]->setLabel($name);
		
		return $this;
	}
	
	public function setParent(Document_Model_Row_Directory $parent) {
		$this->_elements["parent_id"]->setValue($parent->id);
		return $this;
	}
	
	public function setNameLabel($label) {
		$this->_elements["name"]->setLabel($label);
		return $this;
	}
}