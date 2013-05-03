<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setName('login');
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
        	'filters' => array('StringTrim', 'StripTags', 'StringtoLower'),
        	'validators' => array(
        		array('StringLength', false, array(1,50)),
        	),
        	'required' => true,
        	'label' => 'Uživatelské jméno',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('password', 'password', array(
        	'filters' => array('StringTrim', 'StripTags'),
        	'validators' => array(
        		array('StringLength', false, array(1,50)),
        	),
        	'required' => true,
        	'label' => 'Heslo',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'login', array(
        	'required' => false,
        	'ignore' => true,
        	'label' => 'Přihlásit',
        	'decorators' => $elementDecorator2,
        ));
    }


}

