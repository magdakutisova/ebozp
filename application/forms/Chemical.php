<?php
class Application_Form_Chemical extends Zend_Form{
	
	public function init(){
		$this->setName('chemical');
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
		
		$this->addElement('hidden', 'id_chemical', array(
				));
		
		$this->addElement('text', 'chemical', array(
				'label' => 'Název chemické látky',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(1,200)),
				),
				));
		
		$this->addElement('button', 'save_chemical', array(
				'label' => 'Uložit chemickou látku',
				'decorators' => $elementDecorator2,
				));
		
		$this->addElement('hidden', 'clientId', array(
				));
		
	}
	
}