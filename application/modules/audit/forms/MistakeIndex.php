<?php
class Audit_Form_MistakeIndex extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("mistake-filter");
		$this->setMethod(Zend_Form::METHOD_GET);
		$this->setElementsBelongTo("mistake");
		
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$lastDecoratorOpen = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2, "openOnly" => true)),
				array(array("row" => "HtmlTag"), array("tag" => "tr", "openOnly" => true))
		);
		
		$lastDecoratorClose = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2, "closeOnly" => true)),
				array(array("row" => "HtmlTag"), array("tag" => "tr", "closeOnly" => true))
		);
		
		$lastDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement("select", "filter", array(
				"decorators" => $elementDecorator,
				"label" => "Zobrazovat",
				"multioptions" => array("Vše", "Aktuální", "Odstraněné"),
				"value" => 1
		));
		
		$this->addElement("select", "subsidiary_id", array(
				"decorators" => $elementDecorator,
				"label" => "Pobočka",
				"multioptions" => array(0 => "Vše")
		));
		
		$this->addElement("select", "workplace_id", array(
				"decorators" => $elementDecorator,
				"label" => "Pracoviště",
				"multioptions" => array(0 => "Vše")
		));
		
		$this->addElement("select", "category", array(
				"decorators" => $elementDecorator,
				"label" => "Kategorie",
				"multioptions" => array(0 => "Vše")
		));
		
		$this->addElement("select", "subcategory", array(
				"decorators" => $elementDecorator,
				"label" => "Podkategorie",
				"multioptions" => array(0 => "Vše")
		));
		
		$this->addElement("select", "weight", array(
				"decorators" => $elementDecorator,
				"label" => "Závažnost",
				"multioptions" => array("Vše", "1", "2", "3")
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Filtrovat",
				"decorators" => $lastDecorator
		));
	}
	
	/**
	 * naplni seznam pobocek
	 * 
	 * @param Zend_Db_Table_Rowset_Abstract $subsidiaries
	 * @return Audit_Form_MistakeIndex
	 */
	public function addSubsidiaries(Zend_Db_Table_Rowset_Abstract $subsidiaries, $displayAll = true) {
		$element = $this->getElement("subsidiary_id");
		
		if ($displayAll)
			$values = $element->getMultiOptions();
		else
			$values = array();
		
		foreach ($subsidiaries as $item) {
			$values[$item->id_subsidiary] = $item->subsidiary_name . "(" . $item->subsidiary_town . " - " . $item->subsidiary_street . ")";
		}
		
		$element->setMultiOptions($values);
		
		return $this;
	}
	
	/**
	 * naplni seznam pracovist
	 *
	 * @param Zend_Db_Table_Rowset_Abstract $subsidiaries
	 * @return Audit_Form_MistakeIndex
	 */
	public function addWorkplaces(Zend_Db_Table_Rowset_Abstract $workplaces) {
		$element = $this->getElement("workplace_id");
		$values = $element->getMultiOptions();
	
		foreach ($workplaces as $item) {
			$values[$item->id_workplace] = $item->name;
		}
	
		$element->setMultiOptions($values);
		
		// vyhodnoceni hodnoty
		if (!isset($values[$element->getValue()])) $element->setValue(0);
	
		return $this;
	}
}