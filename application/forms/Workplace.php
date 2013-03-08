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
//         $this->addElement('hidden', 'id_position', array(
//        		'value' => 17,
//         	'order' => 1002,
//         	'decorators' => $elementDecorator,
//        	));
        
        $this->addElement('hidden', 'positions', array(
        	'label' => 'Pracovní pozice:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 15,
        	'description' => $questionMarkStart . 'Vyberte pracovní pozice (profese) ze seznamu, nebo, pokud je nenajdete, zadejte oficiální název tak, jak je uveden v pracovní smlouvě' . $questionMarkEnd,
        ));
        $this->getElement('positions')->getDecorator('Description')->setEscape(false);
        
//         $this->addElement('position', 'position', array(
//         	'order' => 16,
//         	'validators' => array(new My_Validate_Position()),
//         ));

        $this->addElement('multiCheckbox', 'positionList', array(
        		'decorators' => $this->generateCheckboxListDecorator('Positions'),
        		'order' => 16,
        		));
        
        $this->addElement('button', 'new_position', array(
        	'label' => 'Přidat novou pracovní pozici',
        	'order' => 100,
        	'decorators' => $elementDecorator2,
        ));
        
        //pracovní činnosti
        /* $this->addElement('hidden', 'id_work', array(
       		'value' => 103,
        	'order' => 1003,
        	'decorators' => $elementDecorator,
        )); */
        
        $this->addElement('hidden', 'works', array(
        	'label' => 'Pracovní činnosti:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 101,
        	'description' => $questionMarkStart . 'Zadejte jednotlivě pracovní činnosti, které se na pracovišti provádějí.' . $questionMarkEnd,
        ));
        $this->getElement('works')->getDecorator('Description')->setEscape(false);
        
       /*  $this->addElement('work', 'work', array(
        	'order' => 102,
        	'validators' => array(new My_Validate_Work()),
        )); */
        
        $this->addElement('multiCheckbox', 'workList', array(
        		'decorators' => $this->generateCheckboxListDecorator('Works'),
        		'order' => 102,
        		));
        
        $this->addElement('button', 'new_work', array(
        	'label' => 'Přidat novou pracovní činnost',
        	'order' => 200,
        	'decorators' => $elementDecorator2,
        ));
        
        //technické prostředky
        $this->addElement('hidden', 'id_technical_device', array(
        	'value' => 203,
        	'order' => 1004,
        	'decorators' => $elementDecorator,
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
        	'decorators' => $elementDecorator,
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

    public function preValidation(array $data, $positionList, $workList, $sortList, $typeList, $chemicalList, $toEdit = false){
    	$newPositions = array_filter(array_keys($data), array($this, 'findPositions'));
    	$newWorks = array_filter(array_keys($data), array($this, 'findWorks'));
    	$newTechnicalDevices = array_filter(array_keys($data), array($this, 'findTechnicalDevices'));
    	$newChemicals = array_filter(array_keys($data), array($this, 'findChemicals'));

    	foreach($newPositions as $fieldName){
     		$order = preg_replace('/\D/', '' , $fieldName) + 1;
     		$newPositionData = isset($data[$fieldName]['new_position']) ? $data[$fieldName]['new_position'] : '';
     		$newPosition = new My_Form_Element_Position('newPosition' . strval($order - 1), array(
    			'order' => $order,
    			'value' => array('id_position' => $data[$fieldName]['id_position'],
    							'position' => $data[$fieldName]['position'],
    							'new_position' => $newPositionData),
    			'validators' => array(new My_Validate_Position()),
    			'multiOptions' => $positionList,
    		));
    		if($toEdit){
    			$newPosition->setAttrib('toEdit', true);
    		}
    		$this->addElement($newPosition);
    	}

    	foreach($newWorks as $fieldName){
   			$order = preg_replace('/\D/', '', $fieldName) + 1;
   			$newWorkData = isset($data[$fieldName]['new_work']) ? $data[$fieldName]['new_work'] : '';
   			$newWork = new My_Form_Element_Work('newWork' . strval($order - 1), array(
   				'order' => $order,
   				'value' => array('id_work' => $data[$fieldName]['id_work'],
   								'work' => $data[$fieldName]['work'],
   								'new_work' => $newWorkData),
   				'validators' => array(new My_Validate_Work()),
   				'multiOptions' => $workList,
   			));
   			if($toEdit){
   				$newWork->setAttrib('toEdit', true);
   			}
   			$this->addElement($newWork);
    	}
    	
    	foreach($newTechnicalDevices as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$newSortData = isset($data[$fieldName]['new_sort']) ? $data[$fieldName]['new_sort'] : '';
    		$newTypeData = isset($data[$fieldName]['new_type']) ? $data[$fieldName]['new_type'] : '';
    		$newTechnicalDevice = new My_Form_Element_TechnicalDevice('newTechnicalDevice' . strval($order - 1), array(
    			'order' => $order,
    			'value' => array('id_technical_device' => $data[$fieldName]['id_technical_device'],
    							'sort' => $data[$fieldName]['sort'],
    							'type' => $data[$fieldName]['type'],
    							'new_sort' => $newSortData,
    							'new_type' => $newTypeData),
    			'validators' => array(new My_Validate_TechnicalDevice()),
    			'multiOptions' => $sortList,
    			'multiOptions2' => $typeList,
    		));
    		if($toEdit){
    			$newTechnicalDevice->setAttrib('toEdit', true);
    		}
    		$this->addElement($newTechnicalDevice);
    	}
    	
    	foreach($newChemicals as $fieldName){
    		$order = preg_replace('/\D/', '', $fieldName) + 1;
    		$newChemicalData = isset($data[$fieldName]['new_chemical']) ? $data[$fieldName]['new_chemical'] : '';
    		$newChemical = new My_Form_Element_ChemicalComplete('newChemical' . strval($order - 1), array(
    			'order' => $order,
    			'value' => array('id_chemical' => $data[$fieldName]['id_chemical'],
    							'chemical' => $data[$fieldName]['chemical'],
    							'new_chemical' => $newChemicalData,
    							'usual_amount' => $data[$fieldName]['usual_amount'],
    							'use_purpose' => $data[$fieldName]['use_purpose']),
    			'validators' => array(new My_Validate_Chemical()),
    			'multiOptions' => $chemicalList,
    		));
    		if($toEdit){
    			$newChemical->setAttrib('toEdit', true);
    		}
    		$this->addElement($newChemical);
    	}
    }
    
	private function findPositions($position){
    	if(strpos($position, 'newPosition') !== false){
    		return $position;
    	}
    }
    
    private function findWorks($work){
    	if(strpos($work, 'newWork') !== false){
    		return $work;
    	}
    }
    
    private function findTechnicalDevices($technicalDevice){
    	if(strpos($technicalDevice, 'newTechnicalDevice') !== false){
    		return $technicalDevice;
    	}
    }
    
	private function findChemicals($chemical){
    	if(strpos($chemical, 'newChemical') !== false){
    		return $chemical;
    	}
    }
    
    private function generateCheckboxListDecorator($name){
    	return array(
    			'ViewHelper',
    			array('Errors'),
    			array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'multiCheckbox' . $name)),
    			array(array('td' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
    			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	);
    }

}

