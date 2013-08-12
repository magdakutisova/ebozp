<?php

class Application_Form_Search extends Zend_Form
{

    public function init()
    {
    	// form decorators
		$this->setDecorators(array(
    		'FormElements',
    		array('HtmlTag',array('tag' => 'div')),
    		'Form'
		));

		// element decorators
		$elementDecorator = array(
    		'ViewHelper',
    		array('Errors'),
    		array(array('row' => 'HtmlTag'), array('tag' => 'span')),
		);    	
		
		$elementDecorator2 = array(
			'ViewHelper',
    		array('Errors'),
    		array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
    		array(array('row' => 'HtmlTag'), array('tag' => 'span')),
		);
    	
    	$this->setName('search');
        
        $this->addElement('text', 'query', array(
        	'filters' => array('StripTags', 'StringTrim', new Zend_Filter_StringToLower('UTF-8')),
        	'decorators' => $elementDecorator,
        		'order' => 1,
        ));
        
        $this->addElement('submit', 'search', array(
        	'label' => 'Hledat',
        	'decorators' => $elementDecorator2,
        		'order' => 100,
        ));
        
        
    }


}

