<?php
class Application_Form_Work extends Zend_Form{
	
	public function init(){
		$this->setName('work');
		$this->setMethod('post');
		
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
		
		$elementDecorator2 = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement('hidden', 'id_work', array(
				));
		
		$this->addElement('text', 'work', array(
				'label' => 'Název pracovní činnosti',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				));
		
		$this->addElement('button', 'save_work', array(
				'decorators' => $elementDecorator2,
				'label' => 'Uložit pracovní činnost',
				));
		
		$this->addElement('hidden', 'clientId', array(
				));
		
	}
	
}