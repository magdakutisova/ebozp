<?php

class Application_Form_Select extends Zend_Form{
	
	public function init(){    
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
       	
       	$this->addElement('select', 'select', array(
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'submit', array(
        	'decorators' => $elementDecorator2,
        ));
	}
	
}