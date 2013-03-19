<?php

class Application_Form_Workplace extends Zend_Form
{

    public function init()
    {
        $this->setName('workplace');
        $this->setMethod('post');
        $this->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
        $this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
        $this->setAttrib('accept-charset', 'utf-8');
        
        $view = Zend_Layout::getMvcInstance()->getView();
        
        $questionMarkStart = '<img src="' . $view->baseUrl('images/question_mark.png') . '" height="20px" width="20px" alt="napoveda" title="';
        $questionMarkEnd = '"/>';
        $hiddenLink = '<a class="showTr">Poznámka</a>';
        
        //dekorátory
       	$elementDecoratorColspan = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 4)),
       		array('Description', array('tag' => 'td')),
       		array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
       		array('Label', array()),
       		array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 1)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	
       	$elementDecoratorColspanSeparator = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 4, 'class' => 'separator')),
       		array('Description', array('tag' => 'td')),
       		array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
       		array('Label', array()),
       		array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 1, 'class' => 'separator')),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	
       	$hiddenDecoratorColspan = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 4)),
       		array('Description', array('tag' => 'td')),
       		array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
       		array('Label', array()),
       		array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 1)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'hidden')),
       	);
       	
       	$elementDecorator = array(
       		'ViewHelper',
       		array('Errors'),
       	);
       	
       	$elementDecorator2 = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 5)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);     	
       	
       	$this->setDecorators(array(
        	'FormElements',
        	array('HtmlTag', array('tag' => 'table')),
        	'Form',
        ));
        
        //elementy        
        $this->addElement('hidden', 'client_id', array(
        	'decorators' => $elementDecorator,
        	'order' => 10000,
        ));
        
        $this->addElement('hidden', 'clientId', array(
        	'value' => $this->getAttrib('clientId'),
        	'order' => 10003,
        ));
       	
       	$this->addElement('hidden', 'id_workplace', array(
       		'decorators' => $elementDecorator,
       		'order' => 10001,
       	));
       	
       	$this->addElement('select', 'subsidiary_id', array(
       		'label' => 'Pobočka',
       		'required' => true,
       		'decorators' => $elementDecoratorColspan,
       		'order' => 1,
       	));
       	      	
       	$this->addElement('text', 'name', array(
       		'label' => 'Název pracoviště',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 2,
       		'description' => $questionMarkStart . 'Zadejte oficiální název pracoviště' . $questionMarkEnd,
       	));       	
       	$this->getElement('name')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('text', 'business_hours', array(
       		'label' => 'Provozní doba pracoviště',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 3,
       		'description' => $questionMarkStart . 'Doba, ve které probíhají na pracovišti práce, a uvedení, zda se jedná o směnný provoz' . $questionMarkEnd,
       	));
       	$this->getElement('business_hours')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('textarea', 'description', array(
       		'label' => 'Popis pracoviště',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 4,
       		'description' => $questionMarkStart . 'Zadejte stručný popis pracoviště a především jeho účel' . $questionMarkEnd,
       	));
       	$this->getElement('description')->getDecorator('Description')->setEscape(false);
       	      	
       	$this->addElement('textarea', 'note', array(
       		'label' => 'Poznámka',
       		'required' => false,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 5,
       	));
       	
       	$username = Zend_Auth::getInstance()->getIdentity()->username;
        $users = new Application_Model_DbTable_User();
        $user = $users->getByUsername($username);
        $acl = new My_Controller_Helper_Acl();
        
        if($acl->isAllowed($user, 'private')){      	
       		$this->addElement('textarea', 'private', array(
       			'label' => 'Soukromá poznámka',
       			'required' => false,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspan,
       			'order' => 6,
       		));
        }
        
        $this->addElement('textarea', 'risks', array(
       		'label' => 'Rizika na pracovišti',
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 7,
       		'description' => $questionMarkStart . 'Popište hlavní rizika, která se na pracovišti vyskytují' . $questionMarkEnd . '<br/>' .  $hiddenLink,
       	));
       	$this->getElement('risks')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('textarea', 'risk_note', array(
       		'label' => 'Poznámka k rizikům',
       		'required' => false,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $hiddenDecoratorColspan,
       		'order' => 8,
       	));
       	
   		if($acl->isAllowed($user, 'private')){      	
       		$this->addElement('textarea', 'risk_private', array(
       			'label' => 'Soukromá poznámka k rizikům',
       			'required' => false,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $hiddenDecoratorColspan,
       			'order' => 9,
       		));
        }
        
        $this->addElement('hidden', 'boss', array(
        	'label' => 'Vedoucí pracoviště',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 10,
        ));
        
        $this->addElement('text', 'boss_name', array(
       		'label' => 'Jméno',
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 11,
        ));
        
        $this->addElement('text', 'boss_surname', array(
        	'label' => 'Příjmení',
        	'filters' => array('StringTrim', 'StripTags'),
        	'decorators' => $elementDecoratorColspan,
        	'order' => 12,
        ));
        
        $this->addElement('text', 'boss_phone', array(
        	'label' => 'Telefon',
        	'filters' => array('StringTrim', 'StripTags'),
        	'decorators' => $elementDecoratorColspan,
        	'order' => 13,
        ));
        
        $this->addElement('text', 'boss_email', array(
        	'label' => 'E-mail',
        	'filters' => array('StringTrim', 'StripTags'),
        	'validators' => array('EmailAddress'),
        	'decorators' => $elementDecoratorColspan,
        	'order' => 14,
        ));
        
		$this->addElement('hidden', 'positions', array(
        	'label' => 'Pracovní pozice:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 15,
        	'description' => $questionMarkStart . 'Vyberte pracovní pozice (profese) ze seznamu, nebo, pokud je nenajdete, zadejte oficiální název tak, jak je uveden v pracovní smlouvě' . $questionMarkEnd,
        ));
        $this->getElement('positions')->getDecorator('Description')->setEscape(false);
        
		$this->addElement('multiCheckbox', 'positionList', array(
        		'decorators' => $this->generateCheckboxListDecorator('Positions'),
        		'order' => 16,
        		));
        
        $this->addElement('button', 'new_position', array(
        	'label' => 'Přidat novou pracovní pozici',
        	'order' => 17,
        	'decorators' => $elementDecorator2,
        ));
        
        $this->addElement('hidden', 'works', array(
        	'label' => 'Pracovní činnosti:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 18,
        	'description' => $questionMarkStart . 'Zadejte jednotlivě pracovní činnosti, které se na pracovišti provádějí.' . $questionMarkEnd,
        ));
        $this->getElement('works')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('multiCheckbox', 'workList', array(
        		'decorators' => $this->generateCheckboxListDecorator('Works'),
        		'order' => 19,
        		));
        
        $this->addElement('button', 'new_work', array(
        	'label' => 'Přidat novou pracovní činnost',
        	'order' => 20,
        	'decorators' => $elementDecorator2,
        ));
        
        $this->addElement('hidden', 'technical_devices', array(
        	'label' => 'Technické prostředky:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 21,
        	'description' => $questionMarkStart . 'Zadejte technologické celky, stroje, nástroje, manipulační nebo dopravní prostředky, které jsou trvale umístěny nebo se opakovaně vyskytují na pracovišti.' . $questionMarkEnd,
        ));
        $this->getElement('technical_devices')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('multiCheckbox', 'technicaldeviceList', array(
        		'decorators' => $this->generateCheckboxListDecorator('Technicaldevices'),
        		'order' => 22,
        		));
        
        $this->addElement('button', 'new_technicaldevice', array(
        	'label' => 'Přidat nový technický prostředek',
        	'order' => 23,
        	'decorators' => $elementDecorator2,
        ));
              	
        //chemické látky
        $this->addElement('hidden', 'id_chemical', array(
        	'value' => 26,
        	'order' => 10002,
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('hidden', 'chemicals', array(
        	'label' => 'Chemické látky:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 24,
        	'description' => $questionMarkStart . 'Zadejte jednotlivě chemické látky, které se používají nebo jsou skladovány na pracovišti. Název, obvyklé množství a její použití.' . $questionMarkEnd,
        ));
        $this->getElement('chemicals')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('multiCheckbox', 'chemicalList', array(
        		'decorators' => $this->generateCheckboxListDecorator('Chemicals'),
        		'order' => 25,
        		));
        
        $this->addElement('button', 'new_chemical', array(
        	'label' => 'Přidat novou chemickou látku',
        	'order' => 2000,
        	'decorators' => $elementDecorator2,
        ));
        
        $this->addElement('select', 'folder_id', array(
        		'label' => 'Zvolte umístění pracoviště',
        		'order' => 2001,
        		'decorators' => $elementDecoratorColspanSeparator,
        		));
        
        $this->addElement('button', 'new_folder', array(
        		'label' => 'Jiné umístění',
        		'order' => 2002,
        		'decorators' => $elementDecorator2,
        		));

        //zbytek
       	$this->addElement('checkbox', 'other', array(
       		'label' => 'Po uložení vložit další pracoviště',
       		'order' => 2003,
       		'decorators' => $elementDecoratorColspan,
       	));
       	
       	$this->addElement('submit', 'save', array(
       		'decorators' => $elementDecorator2,
       		'order' => 2004,	
       	));
    }

    public function preValidation(array $data){
    	$chemicalDetails = array_filter(array_keys($data), array($this, 'findChemicalDetails'));
    	
    	foreach($chemicalDetails as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$chemicalDetail = new My_Form_Element_ChemicalDetail('chemicalDetail' . strval($order - 1), array(
    				'order' => $order,
    				'value' => $data[$fieldName],
    				));
    		$this->addElement($chemicalDetail);
    	}
    }
    
    private function findChemicalDetails($chemicalDetail){
    	if(strpos($chemicalDetail, "chemicalDetail") !== false){
    		return $chemicalDetail;
    	}
    }
       
    private function generateCheckboxListDecorator($name){
    	return array(
    			'ViewHelper',
    			array('Errors'),
    			array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'multiCheckbox' . $name)),
    			array(array('td' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
    			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			array('Label', array()),
    	);
    }

}

