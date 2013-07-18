<?php

class Application_Form_Subsidiary extends Zend_Form
{

    public function init()
    {
        $this->setName('subsidiary');
        $this->setMethod('post');
        
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
        	
        ));
        
        $this->addElement('text', 'subsidiary_name', array(
        	'label' => 'Název pobočky',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(0,255)),
        		),
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
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(1,128)),
        		),
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
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(1,128)),
        		),
        ));
        
        $this->addElement('text', 'district', array(
        		'label' => 'Okres',
        		'required' => false,
        		'filters' => array('StripTags', 'StringTrim'),
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(0,128)),
        		),
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
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(1,64)),
        		),
        ));
        
        $this->addElement('text', 'phone', array(
        	'label' => 'Telefon',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(1,45)),
        		),
        ));
        
        $this->addElement('text', 'email', array(
        	'label' => 'E-mail',
        	'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array('EmailAddress',
        					array('validator' => 'StringLength',
								'options' => array(1,255)),
				),
        	'decorators' => $elementDecorator,
        		
        ));
        
        $this->addElement('text', 'supervision_frequency', array(
        	'label' => 'Četnost dohlídek za rok',
        	'required' => false,
        	'filters' => array('Int', 'StripTags', 'StringTrim'),
        	'validators' => array('Digits'),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'difficulty', array(
        		'label' => 'Náročnost',
        		'required' => false,
        		'filters' => array('StripTags', 'StringTrim'),
        		'validators' => array('Float'),
        		'decorators' => $elementDecorator,
        ));
        
        $this->addElement('text', 'doctor', array(
        	'label' => 'Poskytovatel pracovnělékařské péče',
        	'required' => false,
        	'filters' => array('StripTags', 'StringTrim'),
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(0,255)),
        		),
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('select', 'insurance_company', array(
        		'label' => 'Pojišťovna',
        		'required' => true,
        		'decorators' => $elementDecorator,
        		'multiOptions' => array('Kooperativa', 'Česká pojišťovna'),
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(1,45)),
        		),
        ));
        
     	$username = Zend_Auth::getInstance()->getIdentity()->username;
        $users = new Application_Model_DbTable_User();
        $user = $users->getByUsername($username);
        $acl = new My_Controller_Helper_Acl();
        
        if($acl->isAllowed($user, 'private')){
        	$this->addElement('textarea', 'private', array(
        		'label' => 'Soukromá poznámka',
        		'required' => false,
        		'filters' => array('StripTags', 'StringTrim'),
        		'decorators' => $elementDecorator,
        	));
        }
        
        $this->addElement('checkbox', 'other', array(
        	'label' => 'Přidat další pobočky?',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'save', array(
        	'decorators' => $elementDecorator2,
        ));
    }


}

