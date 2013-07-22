<?php

class Application_Form_Subsidiary extends Zend_Form
{

    public function init()
    {
        $this->setName('subsidiary');
        $this->setMethod('post');
        $this->addPrefixPath('My_Form_Element', 'My/Form/Element', 'element');
        $this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
        $this->setAttrib('accept-charset', 'utf-8');
        
        $this->setDecorators(array(
        	'FormElements',
        	array('HtmlTag', array('tag' => 'table')),
        	'Form',
        ));
        
       	$elementDecorator = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 2)),
       		array('Label', array('tag' => 'td')),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	
       	$elementDecorator2 = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 3)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	
       	$elementSeparatorDecorator = array(
       			'ViewHelper',
       			array('Errors'),
       			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 2, 'class' => 'separator')),
       			array('Description', array('tag' => 'td')),
       			array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
       			array('Label', array()),
       			array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 1, 'class' => 'separator')),
       			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	       
        $this->addElement('hidden', 'id_subsidiary', array(
        	'order' => 1000,
        ));
        
        $this->addElement('hidden', 'id_client', array(
        		'order' => 1001,
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
        	'order' => 1,
        ));
        
         $this->addElement('hidden', 'subsidiary', array(
        	'label' => 'Adresa pobočky',
        	'decorators' => $elementDecorator,
         		'order' => 2,
        ));
        
        $this->addElement('text', 'subsidiary_street', array(
        	'label' => 'Ulice a č. p.',
        	//'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(1,128)),
        		),
        		'order' => 3,
        ));
        
        $this->addElement('text', 'subsidiary_code', array(
        	'label' => 'PSČ',
        	//'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'validators' => array(new Zend_Validate_StringLength(array('min' => 5, 'max => 6')),
        		new Zend_Validate_PostCode('cs_CZ')),
        	'decorators' => $elementDecorator,
        		'order' => 4,
        ));
        
        $this->addElement('text', 'subsidiary_town', array(
        	'label' => 'Obec',
        	//'required' => true,
        	'filters' => array('StripTags', 'StringTrim'),
        	'decorators' => $elementDecorator,
        		'validators' => array(
        				array('validator' => 'StringLength',
        						'options' => array(1,128)),
        		),
        		'order' => 5,
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
        		'order' => 6,
        ));
        
        $this->addElement('text', 'supervision_frequency', array(
        	'label' => 'Četnost dohlídek za rok',
        	'required' => false,
        	'filters' => array('Int', 'StripTags', 'StringTrim'),
        	'validators' => array('Digits'),
        	'decorators' => $elementDecorator,
        		'order' => 7,
        ));
        
        $this->addElement('text', 'difficulty', array(
        		'label' => 'Náročnost',
        		'required' => false,
        		'filters' => array('StripTags', 'StringTrim'),
        		'validators' => array('Float'),
        		'decorators' => $elementDecorator,
        		'order' => 8,
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
        		'order' => 9,
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
        			'order' => 10,
        	));
        }
        
        //kontaktní osoby
        $this->addElement('hidden', 'id_contact_person', array(
        		'value' => 102,
        		'order' => 1002,
        ));
        
        $this->addElement('hidden', 'contact_persons', array(
        		'label' => 'Kontaktní osoby BOZP a PO:',
        		'decorators' => $elementSeparatorDecorator,
        		'order' => 100,
        ));
        
        $this->addElement('contactPerson', 'contactPerson101', array(
        		'order' => 101,
        		'validators' => array(new My_Form_Validator_PersonEmail()),
        		'calledFrom' => 'subs',
        ));
        
        $this->addElement('button', 'new_contact_person_subs', array(
        		'label' => 'Přidat další kontaktní osobu',
        		'order' => 199,
        		'decorators' => $elementDecorator2,
        ));
        
        $this->addElement('hidden', 'id_doctor', array(
        		'value' => 202,
        		'order' => 1003,
        ));
        
        $this->addElement('hidden', 'doctors', array(
        		'label' => 'Poskytovatelé pracovnělékařské péče:',
        		'decorators' => $elementSeparatorDecorator,
        		'order' => 200,
        ));
        
        $this->addElement('doctor', 'doctor201', array(
        		'order' => 201,
        		'validators' => array(new My_Form_Validator_PersonEmail()),
        		'calledFrom' => 'subs',
        ));
        
        $this->addElement('button', 'new_doctor_subs', array(
        		'label' => 'Přidat dalšího poskytovatele pracovnělékařské péče',
        		'order' => 299,
        		'decorators' => $elementDecorator2,
        ));
        
        $this->addElement('hidden', 'id_responsible', array(
        		'value' => 302,
        		'order' => 1004,
        ));
        
        $this->addElement('hidden', 'responsibilities', array(
        		'label' => 'Odpovědné osoby:',
        		'decorators' => $elementSeparatorDecorator,
        		'order' => 300,
        ));
        
        $this->addElement('responsibility', 'responsibility301', array(
        		'order' => 301,
        		'calledFrom' => 'subs',
        ));
        
        $this->addElement('button', 'new_responsible_subs', array(
        		'label' => 'Přidat další odpovědnou osobu',
        		'order' => 997,
        		'decorators' => $elementDecorator2,
        ));
        
        $this->addElement('checkbox', 'other', array(
        	'label' => 'Přidat další pobočky?',
        	'decorators' => $elementDecorator,
        		'order' => 998,
        ));
        
        $this->addElement('submit', 'save', array(
        	'decorators' => $elementDecorator2,
        		'order' => 999,
        ));
    }
    
    public function prevalidation(array $data, $responsibilityList, $employeeList){
    	$newContactPersons = array_filter(array_keys($data), array($this, 'findContactPersons'));
    	$newDoctors = array_filter(array_keys($data), array($this, 'findDoctors'));
    	$newResponsibilities = array_filter(array_keys($data), array($this, 'findResponsibilities'));
    	 
    	foreach($newContactPersons as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$newContactPerson = new My_Form_Element_ContactPerson('newContactPerson' . strval($order - 1), array(
    				'order' => $order,
    				'value' => $data[$fieldName],
    				'validators' => array(new My_Form_Validator_PersonEmail()),
    				'calledFrom' => 'subs',
    		));
    		$this->addElement($newContactPerson);
    	}
    	 
    	foreach($newDoctors as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$newDoctor = new My_Form_Element_Doctor('newDoctor' . strval($order - 1), array(
    				'order' => $order,
    				'value' => $data[$fieldName],
    				'validators' => array(new My_Form_Validator_PersonEmail()),
    				'calledFrom' => 'subs',
    		));
    		$this->addElement($newDoctor);
    	}
    	 
    	foreach($newResponsibilities as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$newResponsibility = new My_Form_Element_Responsibility('newResponsibility' . strval($order - 1), array(
    				'order' => $order,
    				'value' => $data[$fieldName],
    				'multiOptions' => $responsibilityList,
    				'multiOptions2' => $employeeList,
    				'calledFrom' => 'subs',
    		));
    		$this->addElement($newResponsibility);
    	}
    }
    
    private function findContactPersons($contactPerson){
    	if(strpos($contactPerson, 'newContactPerson') !== false){
    		return $contactPerson;
    	}
    }
    
    private function findDoctors($doctor){
    	if(strpos($doctor, 'newDoctor') !== false){
    		return $doctor;
    	}
    }
    
    private function findResponsibilities($responsibility){
    	if(strpos($responsibility, 'newResponsibility') !== false){
    		return $responsibility;
    	}
    }


}

