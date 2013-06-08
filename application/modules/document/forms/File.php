<?php
class Document_Form_File extends Zend_Form {
	
	public function init() {
		$this->setElementsBelongTo("upload");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$submitDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', "colspan" => 2)),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement("text", "name", array("required" => true, "label" => "Jméno : ", "decorators" => $elementDecorator));
		$this->addElement("select", "file_type", array("label" => "Typ souboru : ", "decorators" => $elementDecorator, "multiOptions" => array(
				Document_Model_Row_File::TYPE_NONE => "Obecný dokument",
				Document_Model_Row_File::TYPE_DOCUMENTATION => "Dokumentace",
				Document_Model_Row_File::TYPE_LEGISLATIVE => "Legislativa"
		)));
		
		$this->addElement("submit", "submit", array("label" => "Uložit", "decorators" => $submitDecorator));
	}
}