<?php

class Application_Form_Client extends Zend_Form
{
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
        
        $this->addElement('hidden', 'id_client', array(
        	'filters' => array('Int'),
        ));
        
        $this->addElement('hidden', 'id_subsidiary', array(
        	'filters' => array('Int'),
        ));
        
        $this->addElement('text', 'company_name', array(
        	'label' => 'Název organizace',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('hidden', 'headquarters', array(
        	'label' => 'Adresa sídla',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'headquarters_street', array(
        	'label' => 'Ulice a č. p.',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'headquarters_code', array(
        	'label' => 'PSČ',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array(new Zend_Validate_StringLength(array('min' => 5, 'max => 6')),
        		new Zend_Validate_PostCode('cs_CZ')),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'headquarters_town', array(
        	'label' => 'Obec',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('textarea', 'business', array(
        	'label' => 'Činnost organizace (stručný popis)',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
            'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'company_number', array(
        	'label' => 'IČO',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array('Digits', new Zend_Validate_StringLength(array('min' => 8, 'max => 8'))),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'tax_number', array(
        	'label' => 'DIČ',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array(new Zend_Validate_StringLength(array('max => 15'))),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('select', 'insurance_company', array(
        	'label' => 'Pojišťovna',
        	'required' => true,
        	'decorators' => $elementDecorator,
        	'multiOptions' => array('Kooperativa', 'Česká pojišťovna'),
        ));
        
        $this->addElement('text', 'doctor', array(
        	'label' => 'Poskytovatel pracovnělékařské péče',
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
        
        $this->addElement('textarea', 'private', array(
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

