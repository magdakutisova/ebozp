<?php

class Application_Form_Search extends Zend_Form
{

    public function init()
    {
    	$this->setName('search');
        
        $this->addElement('text', 'query', array(
        	'filters' => array('StripTags', 'StringTrim'),
        ));
        
        $this->addElement('submit', 'submit', array(
        	'label' => 'Hledat',
        ));
    }


}

