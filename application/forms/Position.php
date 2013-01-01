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
       	
       	//zaměstnanci
       	$this->addElement('hidden', 'id_employee', array(
       			'value' => 5,
       			'order' => 1002,
       	));
       	
       	$this->addElement('hidden', 'employees', array(
       			'label' => 'Seznam zaměstnanců:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 3,
       			));
       	
       	$this->addElement('employee', 'employee', array(
       			'order' => 4,
       			'validators' => array(new My_Validate_Employee()),
       			));
       	
       	$this->addElement('button', 'new_employee', array(
       			'label' => 'Další zaměstnanec',
       			'order' => 200,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('submit', 'save', array(
       			'decorators' => $elementDecorator2,
       			'order' => 999,
       	));
	}
	
	public function preValidation(array $data, $yesNoList, $sexList, $yearOfBirthList){
		$newEmployees = array_filter(array_keys($data), array($this,'findEmployees'));
		
		foreach($newEmployees as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newEmployee = new My_Form_Element_Employee('newEmployee' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'validators' => array(new My_Validate_Employee()),
					'multiOptions' => $yesNoList,
					'multiOptions2' => $sexList,
					'multiOptions3' => $yearOfBirthList,
					));
			$this->addElement($newEmployee);
		}
	}
	
	private function findEmployees($employee){
		if(strpos($employee, 'newEmployee') !== false){
			return $employee;
		}
	}
		
}