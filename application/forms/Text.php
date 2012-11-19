<?php

class Application_Form_Text extends Zend_Form{
	
	public function init(){    
		$this->setMethod('post');
		
        $this->setDecorators(array(
        	'FormElements',
        	array('HtmlTag', array('tag' => 'tr')),
        	array('HtmlTag', array('tag' => 'table')),
        	'Form',
        ));
        
       	$elementDecorator = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
       		array('Label', array('tag' => 'td')),
       	);
       	
       	$elementDecorator2 = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
       	);
       	
       	$this->addElement('text', 'text', array(
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'submit', array(
        	'decorators' => $elementDecorator2,
        ));
	}
	
}