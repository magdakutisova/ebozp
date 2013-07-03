<?php

class Application_Form_ResponsibleEmployee extends Zend_Form{
	
	public function init(){
		$this->setName('responsible_employee');
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
		
		$this->addElement('text', 'title_1', array(
				'label' => 'Titul před jménem',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(0,45)),
				),
				));
				
		$this->addElement('text', 'first_name', array(
				'label' => 'Jméno',
				'required' => true,
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(1,45)),
				),
				));
		
		$this->addElement('text', 'surname', array(
				'label' => 'Příjmení',
				'required' => true,
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(1,45)),
				),
				));
		
		$this->addElement('text', 'title_2', array(
				'label' => 'Titul za jménem',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(0,45)),
				),
				));
			
		$this->addElement('text', 'email', array(
        	'label' => 'E-mail',
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array('EmailAddress'),
        	'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(0,255)),
				),
        ));
		 
		$this->addElement('text', 'phone', array(
				'label' => 'Telefon',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(0,45)),
				),
				));
		
		$this->addElement('button', 'save_responsible_employee', array(
				'decorators' => $elementDecorator2,
				'label' => 'Uložit zaměstnance',
		));
		
		$this->addElement('hidden', 'clientId', array(
				));
		
	}
	
}