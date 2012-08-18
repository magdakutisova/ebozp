<?php

class Application_Form_DiaryFilters extends Zend_Form
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
    	
        $this->addElement('select', 'users', array(
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('select', 'subsidiaries', array(
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'filter', array(
        	'label' => 'Filtrovat',
        	'decorators' => $elementDecorator2,
        ));
        
    }


}

