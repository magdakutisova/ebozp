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
		
		$this->addElement("select", "name", array(
				"required" => true,
				"decorators" => $elementDecorator,
				"label" => "Jméno"
		));

		$this->addElement("hidden", "category_name", array("filters" => array(new Zend_Filter_Null())));

		$this->addElement("select", "category_id", array(
			"required" => false,
			"filters" => array(new Zend_Filter_Null()),
			"label" => "Kategorie",
			"multiOptions" => array("" => "-- ŽÁDNÁ KATEGORIE --", "0" => "-- JINÁ KATEGORIE --"),
			"decorators" => $elementDecorator
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
        
        $this->addElement("checkbox", "is_marked", array(
				"required" => false,
				"decorators" => $elementDecorator,
				"label" => "Označit k aktualizaci"
		));
		
		$this->addElement("file", "internal_file", array(
				"required" => false,
				"decorators" => $fileDecorator,
				"label" => "Interní verze (Word)"
				));
		
		$this->addElement("file", "external_file", array(
				"required" => false,
				"decorators" => $fileDecorator,
				"label" => "Veřejná verze (PDF)"
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $submitDecorator
		));
	}

	public function setCategories(array $data) {
		// doplneni dat
		$data = array((string)"" => "-- ŽÁDNÁ KATEGORIE --") + $data + array((string) "0" => "-- JINÁ KATEGORIE --");

		$this->_elements["category_id"]->setMultiOptions($data);
	}
	
	public function setSubsidiaries(array $data) {
		$element = $this->_elements["subsidiary_id"];
		$element->clearMultiOptions();
		$element->addMultiOption("0", "-- Centrální dokumentace --");
		
		foreach ($data as $id => $name) $element->addMultiOption($id, $name);
		
		return $this;
	}
}