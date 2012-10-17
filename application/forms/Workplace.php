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
        
        //dekorátory
       	$elementDecoratorColspan = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 4)),
       		array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
       		array('Label', array()),
       		array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
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
       	
       	//polovina s faktory
       	$this->setDecorators(array(
        	'FormElements',
        	array('HtmlTag', array('tag' => 'table')),
        	'Form',
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
       	));
       	
       	$this->addElement('textarea', 'description', array(
       		'label' => 'Popis pracoviště (jaké pracovní činnosti se zde vykonávají, technická zařízení a technologie...)',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 3,
       	));
       	
       	$this->addElement('textarea', 'note', array(
       		'label' => 'Poznámka',
       		'required' => false,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 4,
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
       			'order' => 5,
       		));
        }
       	
       	$this->addElement('hidden', 'workplaceFactors', array(
       		'label' => 'Faktory pracovního prostředí:',
       		'decorators' => $elementDecoratorColspan,
       		'order' => 6,
       	));
       	
       	$this->addElement('button', 'new_factor', array(
       		'label' => 'Další faktor',
       		'order' => 100,
       		'decorators' => $elementDecorator2,
       	));
       	
       	//rizika 
        $this->addElement('hidden', 'mainRisks', array(
       		'label' => 'Rizika na pracovišti:',
       		'decorators' => $elementDecoratorColspan,
       		'order' => 101,
       	));
       	
       	$this->addElement('button', 'new_risk', array(
       		'label' => 'Další riziko',
       		'order' => 199,
       		'decorators' => $elementDecorator2,
       	));
       	
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

