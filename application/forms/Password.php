<?php

class Application_Form_Password extends Zend_Form
{

    public function init()
    {
        $this->setName('password');
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
        
        $this->addElement('password', 'oldPassword', array(
        	'filters' => array('StringTrim', 'StripTags'),
        	'validators' => array(
        		array('StringLength', false, array(1,50)),
        	),
        	'required' => true,
        	'label' => 'Původní heslo',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('password', 'newPassword', array(
        	'filters' => array('StringTrim', 'StripTags'),
        	'validators' => array(
        		array('StringLength', false, array(1,50)),
        	),
        	'required' => true,
        	'label' => 'Nové heslo',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('password', 'confirmPassword', array(
       		'label' => 'Nové heslo znovu',
       		'required' => true,
       		'filters' => array('StripTags', 'StringTrim'),
       		'validators' => array(
       			'Alnum',
       			array('Identical', false, array('token' => 'newPassword')),
       			array('StringLength', false, array(1,50)),
       		),
       		'decorators' => $elementDecorator,
       	));
        
        $this->addElement('submit', 'submit', array(
        	'required' => false,
        	'ignore' => true,
        	'label' => 'Změnit heslo',
        	'decorators' => $elementDecorator2,
        ));

    }
}

