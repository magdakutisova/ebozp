<?php
class Deadline_Form_Deadline extends Zend_Form {
	
	const TARGET_EMPLOYEE = 1;
	const TARGET_CHEMICAL = 2;
	const TARGET_DEVICE = 3;
	
	public function init() {
		// nastaveni dat
		$this->setName("deadline-form");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("deadline");
		$this->setAction("/deadline/deadline/post");
		
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
		
		$submitDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$this->addElement("select", "subsidiary_id", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Pobočka"
				));
		
		$this->addElement("text", "kind", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Druh"
				));
		
		$this->addElement("text", "specific", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Specifikace"
		));
		
		$this->addElement("text", "type", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Forma"
		));
		
		$this->addElement("textarea", "note", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Poznámka"
		));
		
		$this->addElement("checkbox", "is_period", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Periodické",
				"value" => 1
		));
		
		$this->addElement("text", "period", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Perioda (měsíců)"
		));
		
		$this->addElement("text", "next_date", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Platnost končí:"
		));
		
		$this->addElement("checkbox", "resp_from_guard", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Zodpovídá G U A R D 7, v.o.s.",
				"value" => 1
		));
		
		$this->addElement("select", "responsible_id", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Uživatel"
		));
		
		$this->addElement("select", "workplace_id", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Pracoviště"
				));
		
		$this->addElement("select", "deadline_type", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Lhůta se týká",
				"multiOptions" => array("" => "---VYBERTE---", self::TARGET_EMPLOYEE => "Zaměstnance", self::TARGET_CHEMICAL => "Chemické látky", self::TARGET_DEVICE => "Technického zařízení")
				));
		
		$this->addElement("select", "object_id", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Vyberte objekt",
				"disabled" => "disabled"
				));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $submitDecorator
				));
		
	}
}