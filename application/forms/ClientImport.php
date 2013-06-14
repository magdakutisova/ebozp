<?php

class Application_Form_ClientImport extends Zend_Form{
	
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
       	
       	$this->addElement('hidden', 'hidden', array(
       			'label' => 'Řádky ve tvaru: okres; název firmy; název pobočky; ulice; město; psč; náročnost; četnost; sídlo (bool); sídlo - ulice; sídlo - město; sídlo - psč; soukromá poznámka. Řadit sestupně podle sídlo (bool).',
       			));
       	
       	$this->addElement('textarea', 'textarea', array(
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'submit', array(
        		'label' => 'Odeslat',
        	'decorators' => $elementDecorator2,
        ));
	}
	
}