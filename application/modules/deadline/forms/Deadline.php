<?php
class Deadline_Form_Deadline extends Zend_Form {
	
	const TARGET_EMPLOYEE = 1;
	const TARGET_CHEMICAL = 2;
	const TARGET_DEVICE = 3;
	const TARGET_UNDEFINED = 4;
    const TARGET_ALL = 15;
	
	const RESP_EXTERNAL = 4;
	const RESP_GUARD = 5;
	const RESP_CLIENT = 6;
	
	const TYPE_OTHER = 7;
	const TYPE_PRESENT = 8;
	const TYPE_ELEARNING = 9;
	
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
                array("Description", array("tag" => "span")),
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
				"required" => false,
				"label" => "Pobočka"
				));
		
		$this->addElement("select", "deadline_type", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Lhůta se týká",
				"multiOptions" => array(
						self::TARGET_UNDEFINED => "Jiný typ",
						self::TARGET_EMPLOYEE => "Zaměstnance",
						/* v budoucnu k odebrani self::TARGET_CHEMICAL => "Chemické látky", */
						self::TARGET_DEVICE => "Technického zařízení")
				));
		
        $this->addElement("select", "kind", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Druh",
            "registerInArrayValidator" => true
		));
        
		$this->addElement("select", "type", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Forma",
				"multiOptions" => array(
					self::TYPE_OTHER => "Jiná",
					self::TYPE_ELEARNING => "E-learning",
					self::TYPE_PRESENT => "Prezenční"
				),
            "registerInArrayValidator" => false
		));
		
		$this->addElement("select", "specific", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Specifikace",
            "registerInArrayValidator" => true
		));
		
		$this->addElement("select", "object_id", array(
				"decorators" => $elementDecorator,
				"label" => "Vyberte objekt"
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
				"label" => "Perioda (měsíců)",
                "value" => 0
		));
		
		$this->addElement("textarea", "note", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Poznámka"
		));
		
		$this->addElement("text", "last_done", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Naposledy provedeno:"
		));
		
		$this->addElement("select", "resp_type", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"multiOptions" => array(
						self::RESP_EXTERNAL => "Externista", 
						self::RESP_GUARD => "G U A R D 7, v.o.s.", 
						self::RESP_CLIENT => "Zaměstnanec klienta"),
				"label" => "Provádí",
				"value" => self::RESP_EXTERNAL
		));
		
		$this->addElement("select", "responsible_id", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Uživatel"
		));
		
		$this->addElement("text", "responsible_external_name", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Jméno externisty"
				));
		
		$this->addElement("select", "workplace_id", array(
				"decorators" => $elementDecorator,
				"required" => false,
				"label" => "Pracoviště"
				));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $submitDecorator
				));
		
	}
    
    public function checkKind($kind) {
        $this->getElement("kind")->setDescription("(as)");
    }
    
    protected function _checkValue(Zend_Form_Element_Select $select, $value) {
        if (!in_array($value, $select->getMultiOptions())) {
            $select->setDescription(" (" . $value . ")");
        }
    }
}
