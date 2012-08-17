<?php

class Application_Form_Search extends Zend_Form
{

    public function init()
    {
    	// form decorators
		$this->setDecorators(array(
    		'FormElements',
    		array('HtmlTag',array('tag' => 'table')),
    		'Form'
		));

		// element decorators
		$elementDecorator = array(
    		'ViewHelper',
    		array('Errors'),
    		//array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
    		array(array('row' => 'HtmlTag'), array('tag' => 'td')),
		);    	
		
		$elementDecorator2 = array(
			'ViewHelper',
    		array('Errors'),
    		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
    		array(array('row' => 'HtmlTag'), array('tag' => 'td')),
		);
    	
    	$this->setName('search');
        
        $this->addElement('text', 'query', array(
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'search', array(
        	'label' => 'Hledat',
        	'decorators' => $elementDecorator2,
        ));
    }


}

