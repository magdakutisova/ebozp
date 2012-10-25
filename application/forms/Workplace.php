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
       		array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	
       	$hiddenDecoratorColspan = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 4)),
       		array('Description', array('tag' => 'td')),
       		array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
       		array('Label', array()),
       		array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'hidden')),
       	);
       	
       	$elementDecorator = array(
       		'ViewHelper',
       		array('Errors'),
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
        
        $this->addElement('hidden', 'client_id', array(
        	'decorators' => $elementDecorator,
        ));
       	
       	$this->addElement('hidden', 'id_workplace', array(
       		'decorators' => $elementDecorator,
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
        	'decorators' => $elementDecoratorColspan,
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
       	));
        
        $this->addElement('hidden', 'positions', array(
        	'label' => 'Pracovní pozice:',
        	'decorators' => $elementDecoratorColspan,
        	'order' => 15,
        	'description' => $questionMarkStart . 'Vyberte pracovní pozice (profese) ze seznamu, nebo, pokud je nenajdete, zadejte oficiální název tak, jak je uveden v pracovní smlouvě' . $questionMarkEnd,
        ));
        $this->getElement('positions')->getDecorator('Description')->setEscape(false);
        
        $this->addElement('position', 'position', array(
        	'order' => 16,
        	'multiOptions' => array('test', 'test2'),
        ));
        
        $this->addElement('button', 'new_position', array(
        	'label' => 'Další pracovní pozice',
        	'order' => 100,
        	'decorators' => $elementDecorator2,
        ));
              	
       //další obsah
       	
       	$this->addElement('checkbox', 'other', array(
       		'label' => 'Po uložení vložit další pracoviště',
       		'order' => 200,
       		'decorators' => $elementDecoratorColspan,
       	));
       	
       	$this->addElement('submit', 'save', array(
       		'decorators' => $elementDecorator2,
       		'order' => 201,	
       	));
    }

    public function preValidation(array $data){
    	function findFactors($factor){
    		if(strpos($factor, 'newFactor') !== false){
    			return $factor;
    		}
    	}
    	function findRisks($risk){
    		if(strpos($risk, 'newRisk') !== false){
    			return $risk;
    		}
    	}
    	//Zend_Debug::dump($data);
    	$newFactors = array_filter(array_keys($data), 'findFactors');
    	$newRisks = array_filter(array_keys($data), 'findRisks');

    	foreach($newFactors as $fieldName){
     		$order = preg_replace('/\D/', '' , $fieldName) + 1;
     		$this->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    		$this->addElement('workplaceFactor', 'newFactor' . strval($order - 1), array(
    			'order' => $order,
    			'value' => $data[$fieldName],
    			'validators' => array(new My_Validate_WorkplaceFactor()),
    		));
    	}

    	foreach($newRisks as $fieldName){
   			$order = preg_replace('/\D/', '', $fieldName) + 1;
   			$this->addElement('workplaceRisk', 'newRisk' . strval($order - 1), array(
   				'order' => $order,
   				'value' => $data[$fieldName],
   				'validators' => array(new My_Validate_WorkplaceRisk()),
   			));
    	}
    }

}

