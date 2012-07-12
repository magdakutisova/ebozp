<?php

class Application_Form_Client extends Zend_Form
{
//TODO české hlášky ve formuláři
    public function init()
    {
        $this->setName('client');
        
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
        
        $this->addElement('hidden', 'id', array(
        	'filters' => array('Int'),
        ));
        
        $this->addElement('text', 'companyName', array(
        	'label' => 'Název organizace',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'headquartersAddress', array(
        	'label' => 'Adresa sídla',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('textArea', 'business', array(
        	'label' => 'Činnost organizace (stručný popis)',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
            'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'companyNumber', array(
        	'label' => 'IČO',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array('Digits', new Zend_Validate_StringLength(array('min' => 8, 'max => 8'))),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'taxNumber', array(
        	'label' => 'DIČ',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array(new Zend_Validate_StringLength(array('max => 15'))),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'invoiceAddress', array(
        	'label' => 'Fakturační adresa',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('hidden', 'contact', array(
        	'label' => 'Kontaktní osoba pro BOZP a PO',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'contactPerson', array(
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

