<?php
class Application_Form_Schooling extends Zend_Form{
	
	public function init(){
		$this->setName('schooling');
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
		
		$this->addElement('text', 'schooling', array(
				'label' => 'Název školení',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(1,150)),
				),
				));
		
		$this->addElement('button', 'save_schooling', array(
				'label' => 'Uložit školení',
				'decorators' => $elementDecorator2,
				));
		
		$this->addElement('hidden', 'clientId', array(
				));
	}
	
}