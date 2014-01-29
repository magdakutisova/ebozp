<?php
class Audit_Form_MistakeCreate extends Zend_Form {
	
	public function init() {
		// nastaveni dat
		$this->setName("mistake-post");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setElementsBelongTo("mistake");
        
        $role = Zend_Auth::getInstance()->getIdentity()->role;
	
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
		$this->addElement("select", "category", array(
				"label" => "Kategorie",
				"required" => true,
				"decorators" => $elementDecorator,
				"validators" => array(array("notEmpty")),
				"list" => "categories"
		));
		
		// podkategorie
		$this->addElement("select", "subcategory", array(
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
        if ($role != My_Role::ROLE_CLIENT) {
            $this->addElement("textarea", "hidden_comment", array(
                    "label" => "Skrytý komentář",
                    "decorators" => $elementDecorator
            ));
        }
		
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
				"decorators" => $elementDecorator,
                "value" => 0
		));
		
		$this->addElement("hidden", "record_id", array(
				"decorators" => $lastDecoratorOpen
		));
		
		$this->addElement("submit", "submit", array(
				"label" => "Vytvořit",
				"decorators" => $lastDecoratorClose
		));
		
		$this->initCategories();
	}
	
	public function initCategories() {
		// vytvoreni instance a nacteni vsech kategorii
		$tableCategories = new Audit_Model_Categories();
		$select = $tableCategories->select(true);
		
		$select->order("parent_id")->order("name");
		
		// nacteni dat a rozrazeni do skupin
		$data = $select->query()->fetchAll();
		
		$categories = array();
		$categoryIndex = array();
		$subCategories = array();
		
		foreach ($data as $item) {
			$name = $item["name"];
			
			if ($item["parent_id"]) {
				$parentName = $categoryIndex[$item["parent_id"]];
				
				if (!isset($subCategories[$parentName])) {
					$subCategories[$parentName] = array();
				}
				
				$subCategories[$parentName][$name] = $name;
			} else {
				$categories[$name] = $name;
				
				$categoryIndex[$item["id"]] = $name;
			}
		}
		
		$this->_elements["category"]->setMultiOptions($categories);
		$this->_elements["subcategory"]->setMultiOptions($subCategories);
	}
	
	public function setFilledCategory($category, $subCategory) {
		if ($category == null && $subCategory == null) return;
		
		// nacteni multioptions
		$categories = $this->_elements["category"]->getMultiOptions();
		$subCategories = $this->_elements["subcategory"]->getMultiOptions();
		
		// vyhodnoceni, jestli kategorie existuje
		if (isset($categories[$category])) {
			// kontrola, jestli podkategorie existuje
			if (!isset($subCategories[$category][$subCategory])) {
				$subCategories[$category][$subCategory] = $subCategory;
			}
		} else {
			// zapis nove polozky do seznamu kategorii i podkategorii
			$categories[$category] = $category;
			$subCategories[$subCategory] = $subCategory;
		}
		
		// nastaveni novych hodnot
		$this->_elements["category"]->setMultiOptions($categories);
		$this->_elements["subcategory"]->setMultiOptions($subCategories);
	}
}