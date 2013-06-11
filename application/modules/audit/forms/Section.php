<?php
class Audit_Form_Section extends Zend_Form {
	
	public function init() {
		$this->setElementsBelongTo("section");
		
		// nastaveni dekoratoru
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$formId = $request->getParam("formId");
		
		$url = new Zend_View_Helper_Url();
		$this->setAction($url->url(array("formId" => $formId), "audit-section-post"));
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$lastDecorator = array(
				"ViewHelper",
				array(array("data" => "HtmlTag"), array("tag" => "td", "class" => "element", "colspan" => 2)),
				array(array("row" => "HtmlTag"), array("tag" => "tr"))
		);
		
		$this->addElement("text", "name", array("decorators" => $elementDecorator, "label" => "Název kategorie", "required" => true));
		$this->addElement("submit", "submit", array("decorators" => $lastDecorator, "label" => "Uložit"));
	}
}