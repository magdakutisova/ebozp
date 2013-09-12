<?php
class Deadline_Form_Import extends Zend_Form {
	
	const TYPE_EMPLOYEE = 0;
	const TYPE_DEVICE = 1;
	
	public function init() {
		// nastaveni dat
		$this->setName("deadline-import");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("import");
		$this->setAction("/deadline/deadline/import");
		
		// nastaveni dekoratoru
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
		
		$fileDecorator = array(
				'File',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$submitDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$this->addElement("select", "subsidiary_id", array(
				"label" => "Pobočka",
				"decorators" => $elementDecorator,
				"required" => true
				));
		
		$this->addElement("select", "import_type", array(
				"label" => "Typ objektu",
				"required" => true,
				"decorators" => $elementDecorator,
				"multiOptions" => array(self::TYPE_EMPLOYEE => "Zaměstnanci", self::TYPE_DEVICE => "Zařízení")
		));
		
		$this->addElement("file", "import_file", array(
				"label" => "Soubor s daty",
				"decorators" => $fileDecorator,
				"required" => true
				));
		
		$this->addElement("submit", "submit", array(
				"label" => "Importovat",
				"decorators" => $submitDecorator
				));
	}
	
	/**
	 * nastavi do retezce akce id klienta
	 * @param unknown_type $clientId
	 */
	public function setClientId($clientId) {
		$target = explode("?", $this->getAction());
		
		$newAction = sprintf("%s?clientId=%s", $target[0], $clientId);
		
		$this->setAction($newAction);
	}
}