<?php
class Application_Form_TaskComment extends Zend_Form {
	
	public function init() {
		$this->setName('taskcomment');
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
		
		$this->addElement("textarea", "comment", array(
				"decorators" => $elementDecorator,
				"label" => "Komentář",
				"required" => true
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