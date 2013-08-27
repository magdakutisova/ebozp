<?php
class Audit_Form_MistakeCreate extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("mistake-post");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("mistake");
	
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

		// kategorie
		$this->addElement("text", "category", array(
				"label" => "Kategorie",
				"required" => true,
				"decorators" => $elementDecorator,
				"validators" => array(array("notEmpty")),
				"list" => "categories"
		));
		
		// podkategorie
		$this->addElement("text", "subcategory", array(
				"label" => "Podkategorie",
				"required" => true,
				"decorators" => $elementDecorator,
				"validators" => array(array("notEmpty")),
				"list" => "subcategories"
		));
		
		// konkretizace
		$this->addElement("text", "concretisation", array(
				"label" => "Upřesnění",
				"decorators" => $elementDecorator
		));
		
		// chyba
		$this->addElement("textarea", "mistake", array(
				"label" => "Neshoda",
				"required" => true,
				"decorators" => $elementDecorator
		));
		
		// chyba
		$this->addElement("select", "weight", array(
				"label" => "Závažnost",
				"required" => true,
				"decorators" => $elementDecorator,
				"multiOptions" => array("1" => "1", "2" => "2", "3" => "3")
		));
		
		// navrh reseni
		$this->addElement("textarea", "suggestion", array(
				"label" => "Návrh řešení",
				"required" => true,
				"decorators" => $elementDecorator
		));
		
		// komentar
		$this->addElement("textarea", "comment", array(
				"label" => "Komentář",
				"decorators" => $elementDecorator
		));
		
		// skryty komentar
		$this->addElement("textarea", "hidden_comment", array(
				"label" => "Skrytý komentář",
				"decorators" => $elementDecorator
		));
		
		// zodpovedna osoba
		$this->addElement("text", "responsibile_name", array(
				"label" => "Zodpovědná osoba",
				"required" => true,
				"decorators" => $elementDecorator
		));
		
		// datum odstraneni
		$this->addElement("text", "will_be_removed_at", array(
				"label" => "Navrhovaný termín odstranění",
				"required" => true,
				"decorators" => $elementDecorator,
				"validators" => array(
						array(
								"Regex",
								false,
								array(
										"pattern" => "/^([0-9]{2}\. ){2}[0-9]{4}$/",
										"messages" => "Špatný formát datumu"
								)
						)
				)
		));
		
		// odstraneno neodstraneno
		$this->addElement("checkbox", "is_removed", array(
				"label" => "Odstraněno",
				"required" => true,
				"decorators" => $elementDecorator
		));
		
		$this->addElement("hidden", "record_id", array(
				"decorators" => $lastDecoratorOpen
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Vytvořit",
				"decorators" => $lastDecoratorClose
		));
	}
}