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
        
         $this->addElement('hidden', 'subsidiary', array(
        	'label' => 'Adresa pobočky',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'subsidiary_street', array(
        	'label' => 'Ulice a č. p.',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'subsidiary_code', array(
        	'label' => 'PSČ',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array(new Zend_Validate_StringLength(array('min' => 5, 'max => 6')),
        		new Zend_Validate_PostCode('cs_CZ')),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'subsidiary_town', array(
        	'label' => 'Obec',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
         $this->addElement('hidden', 'invoice', array(
        	'label' => 'Fakturační adresa',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'invoice_street', array(
        	'label' => 'Ulice a č. p.',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'invoice_code', array(
        	'label' => 'PSČ',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array(new Zend_Validate_StringLength(array('min' => 5, 'max => 6')),
        		new Zend_Validate_PostCode('cs_CZ')),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'invoice_town', array(
        	'label' => 'Obec',
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
        
        $this->addElement('textarea', 'private', array(
        	'label' => 'Soukromá poznámka',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('checkbox', 'other', array(
        	'label' => 'Přidat další pobočky?',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'save', array(
        	'decorators' => $elementDecorator2,
        ));
    }


}

