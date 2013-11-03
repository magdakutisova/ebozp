<?php
class Application_Form_Task extends Zend_Form {
	
	public function init() {
		$this->setName('task');
		$this->setMethod('post');
		$this->setAttrib('accept-charset', 'utf-8');
		
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 2)),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement("text", "task", array(
				"decorators" => $elementDecorator,
				"required" => true,
				"label" => "Úkol"
				));
		
		$this->addElement("textarea", "description", array(
				"decorators" => $elementDecorator,
				"label" => "Poznámka"
				));
		
		$this->addElement("checkbox", "global", array(
				"decorators" => $elementDecorator,
				"label" => "Týká se celého klienta"
				));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 2)),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
				)
				));
	}
}