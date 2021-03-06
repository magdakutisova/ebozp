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
		
		//dekorátory
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
		
		$elementDecoratorColspanReverse = array(
				'ViewHelper',
				array('Errors'),
				array('Description', array('tag' => 'td')),
				array('Label', array('placement' => 'append')),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 5)),
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
				'order' => 10010,
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
       	
       	$this->addElement('hidden', 'subsidiaryListError', array(
      			'order' => 2,
       			));
       	
       	$this->addElement('checkbox', 'subsidiariesAll', array(
       			'label' => 'Vybrat všechny pobočky',
       			'decorators' => $elementDecoratorColspanReverse,
       			'order' => 3,
       			));
       	
       	$this->addElement('text', 'position', array(
       			'label' => 'Název pracovní pozice',
       			'required' => true,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 4,
       			'description' => $questionMarkStart . 'Uveďte název uvedený v pracovní smlouvě' . $questionMarkEnd,
       			'validators' => array(
       					array('validator' => 'StringLength',
       							'options' => array(1,128)),
       			),
       	));
       	$this->getElement('position')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('text', 'working_hours', array(
       			'label' => 'Pracovní doba',
       			'order' => 5,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspanSeparator,
       			//'required' => true,
       			'description' => $questionMarkStart . 'Uveďte údaj uvedený v pracovní smlouvě' . $questionMarkEnd,
       	));
       	$this->getElement('working_hours')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'workplaces', array(
        	'label' => 'Pracoviště:',
        	'decorators' => $elementDecoratorColspanSeparator,
        	'order' => 6,
       	));
       	
       	$this->addElement('multiCheckbox', 'workplaceList', array(
       			'decorators' => $this->generateCheckboxListDecorator('Workplaces'),
       			'order' => 7,
       			));
       	
       	$this->addElement('button', 'new_workplace', array(
       			'label' => 'Přidat nové pracoviště',
       			'order' => 8,
       			'decorators' => $elementDecorator2,
       			));
       	
       	$this->addElement('textarea', 'note', array(
       			'label' => 'Poznámka',
       			'required' => false,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspan,
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
       				'filters' => array('StringTrim', 'StripTags'),
       				'decorators' => $elementDecoratorColspan,
       				'order' => 10,
       		));
       	}
       	
       	$this->addElement('select', 'categorization', array(
       			'label' => 'Kategorizace prací pro tuto pracovní pozici provedena',
       			'order' => 11,
       			'decorators' => $elementDecoratorColspan,
       			'multiOptions' => array('0' => 'Ne', '1' => 'Ano'),
       	));
       	
       	//faktory pracovního prostředí       	
       	$this->addElement('hidden', 'id_environment_factor', array(
       			'value' => 14,
       			'order' => 10004,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('hidden', 'environmentFactors', array(
       			'label' => 'Faktory pracovního prostředí:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 12,
       			));
       	
       	$this->addElement('multiCheckbox', 'environmentfactorList', array(
       			'decorators' => $this->generateCheckboxListDecorator('Environmentfactors'),
       			'order' => 13,
       			));
       	
       	//školení
       	$this->addElement('hidden', 'schoolings', array(
       			'label' => 'Školení pro pracovní pozici:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 1001,
       			'description' => $questionMarkStart . 'Vyberte školení ze seznamu (možnost vybrat více možností). Pokud druh školení není uveden, doplňte jej.' . $questionMarkEnd,
       			));
       	$this->getElement('schoolings')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_schooling', array(
       			'value' => 1003,
       			'order' => 10005,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('multiCheckbox', 'schoolingList', array(
       			'order' => 1002,
       			'decorators' => $this->generateCheckboxListDecorator('Schoolings'),
       			));
       	
       	$this->addElement('button', 'new_schooling', array(
       			'label' => 'Přidat nové školení',
       			'order' => 2000,
       			'decorators' => $elementDecorator2,
       			));
       	
       	//pracovní činnosti
       	$this->addElement('hidden', 'works', array(
       			'label' => 'Pracovní činnosti (prováděné práce):',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 2001,
       			'description' => $questionMarkStart . 'Vyberte všechny pracovní činnosti, které zaměstnanec vykonává. Za pracovní činnost se považuje pravidelně se opakující práce.' . $questionMarkEnd,
       			));
       	$this->getElement('works')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_work', array(
       			'value' => 2003,
       			'order' => 10007,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('multiCheckbox', 'workList', array(
       			'decorators' => $this->generateCheckboxListDecorator('Works position'),
       			'order' => 2002,
       			));
       	
       	$this->addElement('button', 'new_work', array(
       			'label' => 'Přidat novou pracovní činnost',
       			'order' => 3000,
       			'decorators' => $elementDecorator2,
       			'class' => array('new_work', 'position', 'background'),
       			));
       	
       	//technické prostředky
       	$this->addElement('hidden', 'technical_devices', array(
       			'label' => 'Technické prostředky:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 3001,
       			'description' => $questionMarkStart . 'Zadejte jednotlivé technologie, stroje, nástroje, dopravní prostředky, nářadí apod. používané nebo obsluhované při této pracovní činnosti.' . $questionMarkEnd,
       			));
       	$this->getElement('technical_devices')->getDecorator('Description')->setEscape(false);
       	       	
       	$this->addElement('multiCheckbox', 'technicaldeviceList', array(
       			'order' => 3002,
       			'decorators' => $this->generateCheckboxListDecorator('Technicaldevices position'),
       			));
       	
       	$this->addElement('button', 'new_technicaldevice', array(
       			'label' => 'Přidat nový technický prostředek',
       			'order' => 3003,
       			'decorators' => $elementDecorator2,
       			'class' => array('new_technicaldevice', 'position', 'background'),
       			));
       	
       	//chemické látky
       	$this->addElement('hidden', 'chemicals', array(
       			'label' => 'Chemické látky:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 3004,
       			'description' => $questionMarkStart . 'Zadejte název chemické látky a její expozici na pracovní pozici.' . $questionMarkEnd,
       			));
       	$this->getElement('chemicals')->getDecorator('Description')->setEscape(false);
       	
       	$this->addElement('hidden', 'id_chemical2', array(
       			'value' => 3006,
       			'order' => 10009,
       			'decorators' => $elementDecorator,
       			));
       	
       	$this->addElement('multiCheckbox', 'chemicalList', array(
       			'order' => 3005,
       			'decorators' => $this->generateCheckboxListDecorator('Chemicals position'),
       			));
       	
       	$this->addElement('button', 'new_chemical', array(
       			'label' => 'Přidat novou chemickou látku',
       			'order' => 4000,
       			'decorators' => $elementDecorator2,
       			'class' => array('new_chemical', 'position', 'background')
       			));
       	
       	//zaměstnanci       	
       	$this->addElement('hidden', 'employees', array(
       			'label' => 'Seznam zaměstnanců:',
       			'decorators' => $elementDecoratorColspanSeparator,
       			'order' => 4001,
       	));
       	
       	$this->addElement('multiCheckbox', 'employeeList', array(
       			'decorators' => $elementDecoratorEmployees,
       			'order' => 4002,
       			));
       	
		$this->addElement('button', 'new_employee', array(
       			'label' => 'Přidat nového zaměstnance',
       			'order' => 5000,
       			'decorators' => $elementDecorator2,
				));
       	
       	//zbytek
       	$this->addElement('checkbox', 'other', array(
       			'label' => 'Po uložení vložit další pracovní pozici',
       			'order' => 9998,
       			'decorators' => $elementDecoratorColspan,
       	));
       	
       	$this->addElement('submit', 'save', array(
       			'decorators' => $elementDecorator2,
       			'order' => 9999,
       	));
	}
	
	public function preValidation(array $data, $canViewPrivate, $categoryList, $yesNoList, $frequencyList){
		$newEnvironmentFactorDetails = array_filter(array_keys($data), array($this, 'findEnvironmentFactorDetails'));
		$newSchoolingDetails = array_filter(array_keys($data), array($this, 'findSchoolingDetails'));
		$newWorkDetails = array_filter(array_keys($data), array($this, 'findWorkDetails'));
		$newChemical2Details = array_filter(array_keys($data), array($this, 'findChemical2Details'));
		
		foreach ($newEnvironmentFactorDetails as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newEnvironmentFactorDetail = new My_Form_Element_EnvironmentFactorDetail('environmentFactorDetail' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'multiOptions' => $categoryList,
					'multiOptions2' => $yesNoList,
					'canViewPrivate' => $canViewPrivate,
					));
			$this->addElement($newEnvironmentFactorDetail);
		}
		
		foreach ($newSchoolingDetails as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newSchoolingDetail = new My_Form_Element_SchoolingDetail('schoolingDetail' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'canViewPrivate' => $canViewPrivate,
					));
			$this->addElement($newSchoolingDetail);
		}
		
		foreach ($newWorkDetails as $fieldName){
			$order = preg_replace('/\D/', '', $fieldName) + 1;
			$newWorkDetail = new My_Form_Element_WorkDetail('workDetail' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					'multiOptions' => $frequencyList,
					));
			$this->addElement($newWorkDetail);
		}
		
		foreach ($newChemical2Details as $fieldName){
			$order = preg_replace('/chemical2Detail/', '', $fieldName) + 1;
			$newChemical2Detail = new My_Form_Element_Chemical2Detail('chemical2Detail' . strval($order - 1), array(
					'order' => $order,
					'value' => $data[$fieldName],
					));
			$this->addElement($newChemical2Detail);
		}
	}
	
	private function findEnvironmentFactorDetails($environmentFactorDetail){
		if(strpos($environmentFactorDetail, 'environmentFactorDetail') !== false){
			return $environmentFactorDetail;
		}
	}
	
	private function findSchoolingDetails($schoolingDetail){
		if(strpos($schoolingDetail, 'schoolingDetail') !== false){
			return $schoolingDetail;
		}
	}
	
	private function findWorkDetails($workDetail){
		if(strpos($workDetail, 'workDetail') !== false){
			return $workDetail;
		}
	}
	
	private function findChemical2Details($chemical2Detail){
		if(strpos($chemical2Detail, 'chemical2Detail') !== false){
			return $chemical2Detail;
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