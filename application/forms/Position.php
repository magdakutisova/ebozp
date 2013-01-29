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
       	
       	$this->addElement('hidden', 'id_position', array(
       		'decorators' => $elementDecorator,
       		'order' => 10001,
       	));
       	
       	$this->addElement('select', 'subsidiary_id', array(
       			'label' => 'Pobočka',
       			'required' => true,
       			'decorators' => $elementDecoratorColspan,
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
       	
       	$this->addElement('textarea', 'note', array(
       			'label' => 'Poznámka',
       			'required' => false,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspan,
       			'order' => 3,
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
       				'order' => 4,
       		));
       	}
       	
       	$this->addElement('hidden', 'employees', array(
       			'label' => 'Seznam zaměstnanců:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 5,
       	));
       	
       	//stávající zaměstnanci
       	$this->addElement('hidden', 'id_current_employee', array(
       			'value' => 7,
       			'order' => 10002,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('currentEmployee', 'current_employee', array(
       			'order' => 6,
       			));
       	
       	$this->addElement('button', 'new_current_employee', array(
       			'label' => 'Přidat dalšího existujícího zaměstnance',
       			'order' => 2000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//noví zaměstnanci
       	$this->addElement('hidden', 'id_employee', array(
       			'value' => 2002,
       			'order' => 10003,
       			'decorators' => $elementDecorator,
       	));      	
       	
       	$this->addElement('employee', 'employee', array(
       			'order' => 2001,
       			'validators' => array(new My_Validate_Employee()),
       			));
       	
       	$this->addElement('button', 'new_employee', array(
       			'label' => 'Přidat dalšího nového zaměstnance',
       			'order' => 3000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('text', 'business_hours', array(
       			'label' => 'Pracovní doba',
       			'order' => 4001,
       			'decorators' => $elementDecoratorColspanSeparator,
       			'required' => true,
       			'description' => $questionMarkStart . 'Uveďte údaj uvedený v pracovní smlouvě' . $questionMarkEnd,
       			));
       	$this->getElement('business_hours')->getDecorator('Description')->setEscape(false);
       	
       	//kategorizace prací a faktory pracovního prostředí
       	$this->addElement('select', 'categorization', array(
       			'label' => 'Kategorizace prací provedena',
       			'order' => 4002,
       			'decorators' => $elementDecoratorColspan,
       			'multiOptions' => array('0' => 'Ne', '1' => 'Ano'),
       			'required' => true,
       			));
       	
       	$this->addElement('hidden', 'environmentFactors', array(
       			'label' => 'Faktory pracovního prostředí:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 4003,
       	));
       	
       	$this->addElement('hidden', 'id_environment_factor', array(
       			'value' => 4005,
       			'order' => 10004,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('environmentFactor', 'environment_factor', array(
       			'order' => 4004,
       			));
       	
       	$this->addElement('button', 'new_environment_factor', array(
       			'label' => 'Další faktor pracovního prostředí',
       			'order' => 5000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//školení
       	$this->addElement('hidden', 'schoolings', array(
       			'label' => 'Školení pro pracovní pozici:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 5001,
       			'description' => $questionMarkStart . 'Vyberte školení ze seznamu (možnost vybrat více možností). Pokud druh školení není uveden, doplňte jej.' . $questionMarkEnd,
       			));
       	$this->getElement('schoolings')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_schooling', array(
       			'value' => 5003,
       			'order' => 10005,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('schooling', 'schooling', array(
       			'order' => 5002,
       			'validators' => array(new My_Validate_Schooling()),
       			));
       	
       	$this->addElement('button', 'new_schooling', array(
       			'label' => 'Další školení',
       			'order' => 5500,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('hidden', 'id_newSchooling', array(
       			'value' => 5501,
       			'order' => 10006,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('button', 'new_newSchooling', array(
       			'label' => 'Zadat neuvedené školení nebo výcvik',
       			'order' => 6000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//pracovní činnosti
       	$this->addElement('hidden', 'works', array(
       			'label' => 'Pracovní činnosti (prováděné práce):',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 6001,
       			'description' => $questionMarkStart . 'Doplňte postupně všechny pracovní činnosti, které zaměstnanec vykonává. Za pracovní činnost se považuje pravidelně se opakující práce.' . $questionMarkEnd,
       			));
       	$this->getElement('works')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_work', array(
       			'value' => 6003,
       			'order' => 10007,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('workComplete', 'work', array(
       			'order' => 6002,
       			'validators' => array(new My_Validate_Work()),
       			));
       	
       	$this->addElement('button', 'new_work_to_position', array(
       			'label' => 'Další pracovní činnost',
       			'order' => 7000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('submit', 'save', array(
       			'decorators' => $elementDecorator2,
       			'order' => 9999,
       	));
	}
	
	public function preValidation(array $data, $yesNoList, $sexList, $yearOfBirthList, $canViewPrivate, $employeeList,
		$environmentFactorList, $categoryList, $schoolingList, $workList, $workplaceList, $frequencyList){
		$newEmployees = array_filter(array_keys($data), array($this,'findEmployees'));
		$newCurrentEmployees = array_filter(array_keys($data), array($this, 'findCurrentEmployees'));
		$newEnvironmentFactors = array_filter(array_keys($data), array($this, 'findEnvironmentFactors'));
		$newSchoolings = array_filter(array_keys($data), array($this, 'findSchoolings'));
		$newNewSchoolings = array_filter(array_keys($data), array($this, 'findNewSchoolings'));
		$newWorks = array_filter(array_keys($data), array($this, 'findWorks'));
		
		foreach($newEmployees as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newEmployee = new My_Form_Element_Employee('newEmployee' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'validators' => array(new My_Validate_Employee()),
					'multiOptions' => $yesNoList,
					'multiOptions2' => $sexList,
					'multiOptions3' => $yearOfBirthList,
					'canViewPrivate' => $canViewPrivate,
					));
			$this->addElement($newEmployee);
		}
		
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
					'multiOptions2' => $workplaceList,
					'multiOptions3' => $frequencyList,
					));
			$this->addElement($newWork);
		}
	}
	
	private function findEmployees($employee){
		if(strpos($employee, 'newEmployee') !== false){
			return $employee;
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
		
}