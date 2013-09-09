<?php
class Document_Form_Documentation extends Zend_Form {
	
	public function init() {
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$this->setMethod(Zend_Form::METHOD_POST)->setElementsBelongTo("documentation")->setName("doc");
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$fileDecorator = array(
				'File',
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
		
		$this->addElement("select", "subsidiary_id", array(
				"required" => true, 
				"decorators" => $elementDecorator,
				"multiOptions" => array(),
				"label" => "Pobočka"
		));
		
		$this->addElement("text", "name", array(
				"required" => true,
				"decorators" => $elementDecorator,
				"label" => "Jméno"
		));
		
		$this->addElement("text", "comment", array(
				"required" => false,
				"decorators" => $elementDecorator,
				"label" => "Komentář"
		));
		
		$this->addElement("textarea", "comment_internal", array(
				"required" => false,
				"decorators" => $elementDecorator,
				"label" => "Interní komentář"
		));
		
		$this->addElement("file", "internal_file", array(
				"required" => false,
				"decorators" => $fileDecorator,
				"label" => "Interní verze"
				));
		
		$this->addElement("file", "external_file", array(
				"required" => false,
				"decorators" => $fileDecorator,
				"label" => "Veřejná verze"
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $submitDecorator
		));
	}
	
	public function setSubsidiaries(array $data) {
		$element = $this->_elements["subsidiary_id"];
		$element->clearMultiOptions();
		$element->addMultiOption("0", "-- Centrální dokumentace --");
		
		foreach ($data as $id => $name) $element->addMultiOption($id, $name);
		
		return $this;
	}
}