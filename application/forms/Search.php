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
		
		$elementDecorator3 = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'element')),
				array(array('row' => 'HtmlTag'), array('tag' => 'div')),
		);
    	
    	$this->setName('search');
        
        $this->addElement('text', 'query', array(
        	'filters' => array('StripTags', 'StringTrim', new Zend_Filter_StringToLower('UTF-8')),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('multiCheckbox', 'active', array(
        		'multiOptions' => array(
        				1 => 'Aktivní pobočky',
        				0 => 'Neaktivní pobočky',
        		),
        		'value' => 1,
        		'decorators' => $elementDecorator3,
        ));
        
        $this->addElement('submit', 'search', array(
        	'label' => 'Hledat',
        	'decorators' => $elementDecorator2,
        ));
        
        
    }


}

