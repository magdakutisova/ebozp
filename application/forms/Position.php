<?php

class Application_Form_Position extends Zend_Form{
	
	public function init(){
		$this->setName('position');
		$this->setMethod('post');
		$this->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
		$this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		$this->setAttrib('accept-charset', 'utf-8');
		
		$view = Zend_Layout::getMvcInstance()->getView();
		
		$questionMarkStart = '<img src="' . $view->baseUrl('images/question_mark.png') . '" height="20px" width="20px" alt="napoveda" title="';
		$questionMarkEnd = '"/>';
		$hiddenLink = '<a class="showTr">Poznámka</a>';
		
		//dekorátory - doplnit
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
		);
		
		$elementDecoratorColspan = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 5)),
				array('Description', array('tag' => 'td')),
				array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
				array('Label', array()),
				array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 1)),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$elementDecoratorColspanSeparator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 5, 'class' => 'separator')),
				array('Description', array('tag' => 'td')),
				array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
				array('Label', array()),
				array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 1, 'class' => 'separator')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$elementDecorator2 = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$elementDecoratorEmployees = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'multiCheckboxEmployees')),
				array(array('td' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
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
				));
       	
       	$this->addElement('hidden', 'id_position', array(
       		'decorators' => $elementDecorator,
       		'order' => 10001,
       	));
       	
       	$this->addElement('multiCheckbox', 'subsidiaryList', array(
       			'label' => 'Pobočky, na kterých se pracovní pozice vyskytuje',
       			'required' => true,
       			'decorators' => $this->generateCheckboxListDecorator('Subsidiaries'),
       			'order' => 1,
       	));
       	
       	$this->addElement('text', 'position', array(
       			'label' => 'Název pracovní pozice',
       			'required' => true,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspan,
       			'order' => 2,
       			'description' => $questionMarkStart . 'Uveďte název uvedený v pracovní smlouvě' . $questionMarkEnd,
       	));
       	$this->getElement('position')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('text', 'business_hours', array(
       			'label' => 'Pracovní doba',
       			'order' => 3,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspanSeparator,
       			'required' => true,
       			'description' => $questionMarkStart . 'Uveďte údaj uvedený v pracovní smlouvě' . $questionMarkEnd,
       	));
       	$this->getElement('business_hours')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('workplace', 'workplace', array(
       			'label' => 'Pracoviště',
       			'order' => 4,
       			));
       	
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
       	
       	$this->addElement('select', 'categorization', array(
       			'label' => 'Kategorizace prací pro tuto pracovní pozici provedena',
       			'order' => 7,
       			'decorators' => $elementDecoratorColspan,
       			'multiOptions' => array('0' => 'Ne', '1' => 'Ano'),
       	));
       	
       	//faktory pracovního prostředí       	
       	$this->addElement('hidden', 'environmentFactors', array(
       			'label' => 'Faktory pracovního prostředí:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 1001,
       	));
       	
       	$this->addElement('hidden', 'id_environment_factor', array(
       			'value' => 1003,
       			'order' => 10004,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('environmentFactor', 'environment_factor', array(
       			'order' => 1002,
       			));
       	
       	$this->addElement('button', 'new_environment_factor', array(
       			'label' => 'Další faktor pracovního prostředí',
       			'order' => 2000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//školení
       	$this->addElement('hidden', 'schoolings', array(
       			'label' => 'Školení pro pracovní pozici:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 2001,
       			'description' => $questionMarkStart . 'Vyberte školení ze seznamu (možnost vybrat více možností). Pokud druh školení není uveden, doplňte jej.' . $questionMarkEnd,
       			));
       	$this->getElement('schoolings')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_schooling', array(
       			'value' => 2004,
       			'order' => 10005,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('schooling', 'schooling', array(
       			'order' => 2002,
       			'validators' => array(new My_Validate_Schooling()),
       			));
       	
       	$this->addElement('schooling', 'schooling2', array(
       			'order' => 2003,
       			'validators' => array(new My_Validate_Schooling()),
       			));
       	
       	$this->addElement('button', 'new_schooling', array(
       			'label' => 'Další školení',
       			'order' => 2500,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('hidden', 'id_newSchooling', array(
       			'value' => 2501,
       			'order' => 10006,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('button', 'new_newSchooling', array(
       			'label' => 'Zadat neuvedené školení nebo výcvik',
       			'order' => 3000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//pracovní činnosti
       	$this->addElement('hidden', 'works', array(
       			'label' => 'Pracovní činnosti (prováděné práce):',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 3001,
       			'description' => $questionMarkStart . 'Doplňte postupně všechny pracovní činnosti, které zaměstnanec vykonává. Za pracovní činnost se považuje pravidelně se opakující práce.' . $questionMarkEnd,
       			));
       	$this->getElement('works')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_work', array(
       			'value' => 3003,
       			'order' => 10007,
       			'decorators' => $elementDecorator,
       			));
       	
       /* 	$this->addElement('workComplete', 'work', array(
       			'order' => 3002,
       			'validators' => array(new My_Validate_Work()),
       			)); */
       	
       	$this->addElement('button', 'new_work_to_position', array(
       			'label' => 'Další pracovní činnost',
       			'order' => 4000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//technické prostředky
       	$this->addElement('hidden', 'technical_devices', array(
       			'label' => 'Technické prostředky:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 4001,
       			'description' => $questionMarkStart . 'Zadejte jednotlivé technologie, stroje, nástroje, dopravní prostředky, nářadí apod. používané nebo obsluhované při této pracovní činnosti.' . $questionMarkEnd,
       			));
       	$this->getElement('technical_devices')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_technical_device', array(
       			'value' => 4003,
       			'order' => 10008,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('technicalDevice', 'technical_device', array(
       			'order' => 4002,
       			'validators' => array(new My_Validate_TechnicalDevice()),
       			));
       	
       	$this->addElement('button', 'new_technical_device_to_position', array(
       			'label' => 'Další technický prostředek',
       			'order' => 5000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//chemické látky
       	$this->addElement('hidden', 'chemicals', array(
       			'label' => 'Chemické látky:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 5001,
       			'description' => $questionMarkStart . 'Zadejte název chemické látky a její expozici na pracovní pozici.' . $questionMarkEnd,
       			));
       	$this->getElement('chemicals')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_chemical', array(
       			'value' => 5003,
       			'order' => 10009,
       			'decorators' => $elementDecorator,
       			));
       	
       	/* $this->addElement('chemical', 'chemical', array(
       			'order' => 5002,
       			'validators' => array(new My_Validate_Chemical()),
       			)); */
       	
       	$this->addElement('button', 'new_chemical_to_position', array(
       			'label' => 'Další chemická látka',
       			'order' => 6000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//zaměstnanci       	
       	$this->addElement('hidden', 'employees', array(
       			'label' => 'Seznam zaměstnanců:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 6001,
       	));
       	
       	$this->addElement('multiCheckbox', 'employeeList', array(
       			'decorators' => $elementDecoratorEmployees,
       			'order' => 6002,
       			));
       	
//        	//stávající zaměstnanci       	
//        	$this->addElement('currentEmployee', 'current_employee', array(
//        			'order' => 6002,
//        	));
       	
//        	$this->addElement('hidden', 'id_current_employee', array(
//        			'value' => 6003,
//        	));
       	
//        	$this->addElement('button', 'new_current_employee', array(
//        			'label' => 'Přidat dalšího existujícího zaměstnance',
//        			'order' => 7000,
//        			'decorators' => $elementDecorator2,
//        	));
       	
       	$this->addElement('button', 'new_employee', array(
       			'label' => 'Přidat nového zaměstnance',
       			'order' => 8000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('submit', 'save', array(
       			'decorators' => $elementDecorator2,
       			'order' => 9999,
       	));
	}
	
	public function preValidation(array $data, $canViewPrivate, $employeeList,
		$environmentFactorList, $categoryList, $schoolingList, $workList, $frequencyList, $sortList,
			$typeList, $chemicalList, $yesNoList){
		$newCurrentEmployees = array_filter(array_keys($data), array($this, 'findCurrentEmployees'));
		$newEnvironmentFactors = array_filter(array_keys($data), array($this, 'findEnvironmentFactors'));
		$newSchoolings = array_filter(array_keys($data), array($this, 'findSchoolings'));
		$newNewSchoolings = array_filter(array_keys($data), array($this, 'findNewSchoolings'));
		$newWorks = array_filter(array_keys($data), array($this, 'findWorks'));
		$newTechnicalDevices = array_filter(array_keys($data), array($this, 'findTechnicalDevices'));
		$newChemicals = array_filter(array_keys($data), array($this, 'findChemicals'));
		
		foreach($newCurrentEmployees as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newCurrentEmployee = new My_Form_Element_CurrentEmployee('newCurrentEmployee' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'multiOptions' => $employeeList,
					));
			$this->addElement($newCurrentEmployee);
		}
		
		foreach($newEnvironmentFactors as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newEnvironmentFactor = new My_Form_Element_EnvironmentFactor('newEnvironmentFactor' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'multiOptions' => $environmentFactorList,
					'multiOptions2' => $categoryList,
					'multiOptions3' => $yesNoList,
					'canViewPrivate' => $canViewPrivate,
					));
			$this->addElement($newEnvironmentFactor);
		}
		
		foreach($newSchoolings as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newSchooling = new My_Form_Element_Schooling('newSchooling' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'validators' => array(new My_Validate_Schooling()),
					'multiOptions' => $schoolingList,
					'canViewPrivate' => $canViewPrivate,
					));
			$this->addElement($newSchooling);
		}
		
		foreach($newNewSchoolings as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newNewSchooling = new My_Form_Element_NewSchooling('newNewSchooling' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'validators' => array(new My_Validate_Schooling()),
					'canViewPrivate' => $canViewPrivate,
					));
			$this->addElement($newNewSchooling);
		}
		
		foreach($newWorks as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newWork = new My_Form_Element_WorkComplete('newWork' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'validators' => array(new My_Validate_Work()),
					'multiOptions' => $workList,
					'multiOptions2' => $frequencyList,
					));
			$this->addElement($newWork);
		}
		
		foreach($newTechnicalDevices as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newTechnicalDevice = new My_Form_Element_TechnicalDevice('newTechnicalDevice' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'validators' => array(new My_Validate_TechnicalDevice()),
					'multiOptions' => $sortList,
					'multiOptions2' => $typeList,
					));
			$this->addElement($newTechnicalDevice);
		}
		
		foreach($newChemicals as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newChemical = new My_Form_Element_Chemical('newChemical' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'validators' => array(new My_Validate_Chemical()),
					'multiOptions' => $chemicalList,
					));
			$this->addElement($newChemical);
		}
	}
	
	private function findCurrentEmployees($currentEmployee){
		if(strpos($currentEmployee, 'newCurrentEmployee') !== false){
			return $currentEmployee;
		}
	}
	
	private function findEnvironmentFactors($environmentFactor){
		if(strpos($environmentFactor, 'newEnvironmentFactor') !== false){
			return $environmentFactor;
		}
	}
	
	private function findSchoolings($schooling){
		if(preg_match('/newSchooling\w+/', $schooling)){
			return $schooling;
		}
	}
	
	private function findNewSchoolings($newSchooling){
		if(strpos($newSchooling, 'newNewSchooling') !== false){
			return $newSchooling;
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
				array('Label', array()),
		);
	}
		
}