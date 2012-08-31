<?php

class Application_Form_Workplace extends Zend_Form
{

    public function init()
    {
        $this->setName('workplaceFactors');
        $this->setMethod('post');
        $this->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
        
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
       	
       	$this->addElement('hidden', 'id_factor', array(
       		'value' => 18,
       	));
       	      	
       	$this->addElement('text', 'name', array(
       		'label' => 'Název pracoviště',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 1,
       	));
       	
       	$this->addElement('textarea', 'description', array(
       		'label' => 'Popis pracoviště (jaké pracovní činnosti se zde vykonávají, technická zařízení a technologie...)',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 2,
       	));
       	
       	$this->addElement('textarea', 'note', array(
       		'label' => 'Poznámka',
       		'required' => false,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 3,
       	));
       	
       	$this->addElement('textarea', 'private', array(
       		'label' => 'Soukromá poznámka',
       		'required' => false,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 4,
       	));
       	
       	$this->addElement('hidden', 'workplaceFactors', array(
       		'label' => 'Faktory pracovního prostředí:',
       		'decorators' => $elementDecoratorColspan,
       		'order' => 5,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor1', array(
       		'factor' => 'Prach',
       		'order' => 6,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor2', array(
       		'factor' => 'Chemické látky',
       		'order' => 7,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor3', array(
       		'factor' => 'Hluk',
       		'order' => 8,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor4', array(
       		'factor' => 'Vibrace',
       		'order' => 9,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor5', array(
       		'factor' => 'Neionizující záření a elektromagnetická pole',
       		'order' => 10,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor6', array(
       		'factor' => 'Fyzická zátěž',
       		'order' => 11,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor7', array(
       		'factor' => 'Pracovní poloha',
       		'order' => 12,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor8', array(
       		'factor' => 'Zátěž teplem',
       		'order' => 13,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor9', array(
       		'factor' => 'Zátěž chladem',
       		'order' => 14,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor10', array(
       		'factor' => 'Psychická zátěž',
       		'order' => 15,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor11', array(
       		'factor' => 'Zraková zátěž',
       		'order' => 16,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor12', array(
       		'factor' => 'Práce s biologickými činiteli',
       		'order' => 17,
       	));
       	
       	$this->addElement('workplaceFactor', 'factor13', array(
       		'factor' => 'Práce ve zvýšeném tlaku vzduchu',
       		'order' => 18,
       	));
       	
       	$this->addElement('button', 'new_factor', array(
       		'label' => 'Další faktor',
       		'order' => 100,
       		'decorators' => $elementDecorator2,
       	));
       	
       	//rizika
		$this->addElement('hidden', 'id_risk', array(
       		'value' => 103,
       	));
        
        $this->addElement('hidden', 'mainRisks', array(
       		'label' => 'Rizika na pracovišti:',
       		'decorators' => $elementDecoratorColspan,
       		'order' => 101,
       	));
       	
       	$this->addElement('workplaceRisk', 'risk102', array(
       		'order' => 102,
       	));
       	
       	$this->addElement('button', 'new_risk', array(
       		'label' => 'Další riziko',
       		'order' => 199,
       		'decorators' => $elementDecorator2,
       	));
       	
       	$this->addElement('submit', 'save', array(
       		'decorators' => $elementDecorator2,
       		'order' => 200,	
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
    	
    	$newFactors = array_filter(array_keys($data), 'findFactors');
    	$newRisks = array_filter(array_keys($data), 'findRisks');
    	
    	foreach($newFactors as $fieldName){
    		$order = ltrim($fieldName, 'newFactor') + 1;
    		$this->addNewField('workplaceFactor', $fieldName, $order);
    	}
    	
    	foreach($newRisks as $fieldName){
    		$order = ltrim($fieldName, 'newRisk') + 1;
    		$this->addNewField('workplaceRisk', $fieldName, $order);
    	}
    }
    
    public function addNewField($element, $name, $order){
    	if($element == 'workplaceFactor'){
    		$this->addElement($element, $name, array(
    			'order' => $order,
    		));
    	}
    	if($element == 'workplaceRisk'){
    		$this->addElement($element, $name, array(
    			'order' => $order,
    		));
    	}
    }

}

