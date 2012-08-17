<?php

class Application_Form_DiaryFilters extends Zend_Form
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
    		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
    		array('Label', array('tag' => 'td')),
    		array(array('row' => 'HtmlTag'), array('tag' => 'td')),
		);    	
		
		$elementDecorator2 = array(
			'ViewHelper',
    		array('Errors'),
    		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
    		array(array('row' => 'HtmlTag'), array('tag' => 'td')),
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

