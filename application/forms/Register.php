<?php

class Application_Form_Register extends Zend_Form{
	
	public function init(){
		
		$this->setName('register');
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
       	
       	$this->addElement('text', 'username', array(
       		'label' => 'Uživatelské jméno',
       		'required' => true,
       		'filters' => array('StripTags', 'StringTrim', 'StringtoLower'),
       		'validators' => array(
       			'Alnum',
       			array('StringLength', false, array(1,50)),
       		),
       		'decorators' => $elementDecorator,
       	));
       	
       	$this->addElement('text', 'name', array(
       			'label' => 'Jméno a příjmení',
       			'required' => true,
       			'filters' => array('StripTags', 'StringTrim'),
       			'validators' => array(
       					array('StringLength', false, array(1,120)),
       					),
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('password', 'password', array(
       		'label' => 'Heslo',
       		'required' => true,
       		'filters' => array('StripTags', 'StringTrim'),
       		'validators' => array(
       			'Alnum',
       			array('StringLength', false, array(1,50)),
       		),
       		'decorators' => $elementDecorator,
       	));
       	
       	$this->addElement('password', 'confirmPassword', array(
       		'label' => 'Heslo znovu',
       		'required' => true,
       		'filters' => array('StripTags', 'StringTrim'),
       		'validators' => array(
       			'Alnum',
       			array('Identical', false, array('token' => 'password')),
       			array('StringLength', false, array(1,50)),
       		),
       		'decorators' => $elementDecorator,
       	));
       	      	
       	$this->addElement('select', 'role', array(
       		'label' => 'Práva',
       		'multiOptions' => My_Role::getRoles(),
       		'decorators' => $elementDecorator,
       	));
       	
       	$this->addElement('submit', 'create', array(
       		'label' => 'Vytvořit',
       		'decorators' => $elementDecorator2,
       	));
       	
       	$this->addElement('hash', 'csrf', array(
       		'ignore' => true,
       	));
		
	}
	
}