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
        	'order' => 1000,
        ));
       	
       	$this->addElement('hidden', 'id_position', array(
       		'decorators' => $elementDecorator,
       		'order' => 1001,
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
       			'order' => 1002,
       			));
       	
       	$this->addElement('currentEmployee', 'current_employee', array(
       			'order' => 6,
       			));
       	
       	$this->addElement('button', 'new_current_employee', array(
       			'label' => 'Přidat dalšího existujícího zaměstnance',
       			'order' => 100,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//noví zaměstnanci
       	$this->addElement('hidden', 'id_employee', array(
       			'value' => 202,
       			'order' => 1003,
       	));      	
       	
       	$this->addElement('employee', 'employee', array(
       			'order' => 201,
       			'validators' => array(new My_Validate_Employee()),
       			));
       	
       	$this->addElement('button', 'new_employee', array(
       			'label' => 'Přidat dalšího nového zaměstnance',
       			'order' => 400,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('text', 'business_hours', array(
       			'label' => 'Pracovní doba',
       			'order' => 401,
       			'decorators' => $elementDecoratorColspanSeparator,
       			'required' => true,
       			'description' => $questionMarkStart . 'Uveďte údaj uvedený v pracovní smlouvě' . $questionMarkEnd,
       			));
       	$this->getElement('business_hours')->getDecorator('Description')->setEscape(false);
       	
       	//kategorizace prací a faktory pracovního prostředí
       	$this->addElement('select', 'categorization', array(
       			'label' => 'Kategorizace prací provedena',
       			'order' => 402,
       			'decorators' => $elementDecoratorColspan,
       			'multiOptions' => array('0' => 'Ne', '1' => 'Ano'),
       			'required' => true,
       			));
       	
       	$this->addElement('hidden', 'environmentFactors', array(
       			'label' => 'Faktory pracovního prostředí:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 403,
       	));
       	
       	$this->addElement('hidden', 'id_environment_factor', array(
       			'value' => 405,
       			'order' => 1004,
       			));
       	
       	$this->addElement('environmentFactor', 'environment_factor', array(
       			'order' => 404,
       			));
       	
       	$this->addElement('button', 'new_environment_factor', array(
       			'label' => 'Další faktor pracovního prostředí',
       			'order' => 500,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('submit', 'save', array(
       			'decorators' => $elementDecorator2,
       			'order' => 999,
       	));
	}
	
	public function preValidation(array $data, $yesNoList, $sexList, $yearOfBirthList, $canViewPrivate, $employeeList){
		$newEmployees = array_filter(array_keys($data), array($this,'findEmployees'));
		$newCurrentEmployees = array_filter(array_keys($data), array($this, 'findCurrentEmployees'));
		
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
		
}