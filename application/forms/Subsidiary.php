<?php

class Application_Form_Subsidiary extends Zend_Form
{

    public function init()
    {
        $this->setName('subsidiary');
        
        //TODO refaktoring dekorátorů
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
       	       
        $this->addElement('hidden', 'id_subsidiary', array(
        	'filters' => array('Int'),
        ));
        
        $this->addElement('text', 'subsidiary_name', array(
        	'label' => 'Název pobočky',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'subsidiary_address', array(
        	'label' => 'Adresa sídla',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'invoice_address', array(
        	'label' => 'Fakturační adresa',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('hidden', 'contact', array(
        	'label' => 'Kontaktní osoba pro BOZP a PO',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'contact_person', array(
        	'label' => 'Jméno a příjmení',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'phone', array(
        	'label' => 'Telefon',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'email', array(
        	'label' => 'E-mail',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array('EmailAddress'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'supervision_frequency', array(
        	'label' => 'Četnost dohlídek za rok',
        	'required' => false,
        	'filters' => array('Int', 'StripTags', 'StringTrim'),
        	'validators' => array('Digits'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('textArea', 'private', array(
        	'label' => 'Soukromá poznámka',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'save', array(
        	'decorators' => $elementDecorator2,
        ));
    }


}

