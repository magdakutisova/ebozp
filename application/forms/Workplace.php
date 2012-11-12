<?php

class Application_Form_Workplace extends Zend_Form
{

    public function init()
    {
        $this->setName('workplace');
        $this->setMethod('post');
        $this->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
        $this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
        
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
        	'order' => 1000,
        ));
       	
       	$this->addElement('hidden', 'id_workplace', array(
       		'decorators' => $elementDecorator,
       		'order' => 1001,
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
        	'decorators' => $elementDecoratorColspan,
        	'order' => 14,
        ));
        
        //pracovní pozice
        $this->addElement('hidden', 'id_position', array(
       		'value' => 17,
        	'order' => 1002,
       	));
        
        $this->addElement('hidden', 'positions', array(
        	'label' => 'Pracovní pozice:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 15,
        	'description' => $questionMarkStart . 'Vyberte pracovní pozice (profese) ze seznamu, nebo, pokud je nenajdete, zadejte oficiální název tak, jak je uveden v pracovní smlouvě' . $questionMarkEnd,
        ));
        $this->getElement('positions')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('position', 'position', array(
        	'order' => 16,
        	'validators' => array(new My_Validate_Position()),
        ));
        
        $this->addElement('button', 'new_position', array(
        	'label' => 'Další pracovní pozice',
        	'order' => 100,
        	'decorators' => $elementDecorator2,
        ));
        
        //pracovní činnosti
        $this->addElement('hidden', 'id_work', array(
       		'value' => 103,
        	'order' => 1003,
        ));
        
        $this->addElement('hidden', 'works', array(
        	'label' => 'Pracovní činnosti:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 101,
        	'description' => $questionMarkStart . 'Zadejte jednotlivě pracovní činnosti, které se na pracovišti provádějí.' . $questionMarkEnd,
        ));
        $this->getElement('works')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('work', 'work', array(
        	'order' => 102,
        	'validators' => array(new My_Validate_Work()),
        ));
        
        $this->addElement('button', 'new_work', array(
        	'label' => 'Další pracovní činnost',
        	'order' => 200,
        	'decorators' => $elementDecorator2,
        ));
        
        //technické prostředky
        $this->addElement('hidden', 'id_technical_device', array(
        	'value' => 203,
        	'order' => 1004,
        ));
        
        $this->addElement('hidden', 'technical_devices', array(
        	'label' => 'Technické prostředky:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 201,
        	'description' => $questionMarkStart . 'Zadejte technologické celky, stroje, nástroje, manipulační nebo dopravní prostředky, které jsou trvale umístěny nebo se opakovaně vyskytují na pracovišti.' . $questionMarkEnd,
        ));
        $this->getElement('technical_devices')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('technicalDevice', 'technical_device', array(
        	'order' => 202,
        	'validators' => array(new My_Validate_TechnicalDevice()),
        ));
        
        $this->addElement('button', 'new_technical_device', array(
        	'label' => 'Další technický prostředek',
        	'order' => 300,
        	'decorators' => $elementDecorator2,
        ));
              	
        //chemické látky
        $this->addElement('hidden', 'id_chemical', array(
        	'value' => 303,
        	'order' => 1005,
        ));
        
        $this->addElement('hidden', 'chemicals', array(
        	'label' => 'Chemické látky:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 301,
        	'description' => $questionMarkStart . 'Zadejte jednotlivě chemické látky, které se používají nebo jsou skladovány na pracovišti. Název, obvyklé množství a její použití.' . $questionMarkEnd,
        ));
        $this->getElement('chemicals')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('chemicalComplete', 'chemical', array(
        	'order' => 302,
        	'validators' => array(new My_Validate_Chemical()),
        ));
        
        $this->addElement('button', 'new_chemical', array(
        	'label' => 'Další chemická látka',
        	'order' => 400,
        	'decorators' => $elementDecorator2,
        ));

        //zbytek
       	$this->addElement('checkbox', 'other', array(
       		'label' => 'Po uložení vložit další pracoviště',
       		'order' => 998,
       		'decorators' => $elementDecoratorColspanSeparator,
       	));
       	
       	$this->addElement('submit', 'save', array(
       		'decorators' => $elementDecorator2,
       		'order' => 999,	
       	));
    }

    public function preValidation(array $data, $positionList, $workList, $sortList, $typeList, $chemicalList){
    	function findPositions($position){
    		if(strpos($position, 'newPosition') !== false){
    			return $position;
    		}
    	}
    	function findWorks($work){
    		if(strpos($work, 'newWork') !== false){
    			return $work;
    		}
    	}
    	function findTechnicalDevices($technicalDevice){
    		if(strpos($technicalDevice, 'newTechnicalDevice') !== false){
    			return $technicalDevice;
    		}
    	}
    	function findChemicals($chemical){
    		if(strpos($chemical, 'newChemical') !== false){
    			return $chemical;
    		}
    	}
    	$newPositions = array_filter(array_keys($data), 'findPositions');
    	$newWorks = array_filter(array_keys($data), 'findWorks');
    	$newTechnicalDevices = array_filter(array_keys($data), 'findTechnicalDevices');
    	$newChemicals = array_filter(array_keys($data), 'findChemicals');

    	foreach($newPositions as $fieldName){
     		$order = preg_replace('/\D/', '' , $fieldName) + 1;
     		$this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    		$this->addElement('position', 'newPosition' . strval($order - 1), array(
    			'order' => $order,
    			'value' => $data[$fieldName],
    			'validators' => array(new My_Validate_Position()),
    			'multiOptions' => $positionList,
    		));
    	}

    	foreach($newWorks as $fieldName){
   			$order = preg_replace('/\D/', '', $fieldName) + 1;
   			$this->addElement('work', 'newWork' . strval($order - 1), array(
   				'order' => $order,
   				'value' => $data[$fieldName],
   				'validators' => array(new My_Validate_Work()),
   				'multiOptions' => $workList,
   			));
    	}
    	
    	foreach($newTechnicalDevices as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$this->addElement('technicalDevice', 'newTechnicalDevice' . strval($order - 1), array(
    			'order' => $order,
    			'value' => $data[$fieldName],
    			'validators' => array(new My_Validate_TechnicalDevice()),
    			'multiOptions' => $sortList,
    			'multiOptions2' => $typeList,
    		));
    	}
    	
    	foreach($newChemicals as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$this->addElement('chemicalComplete', 'newChemical' . strval($order - 1), array(
    			'order' => $order,
    			'value' => $data[$fieldName],
    			'validators' => array(new My_Validate_Chemical()),
    			'multiOptions' => $chemicalList,
    		));
    	}
    }

}

