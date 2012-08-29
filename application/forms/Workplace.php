<?php

class Application_Form_Workplace extends Zend_Form
{

    public function init()
    {
        $this->setName('workplace');
        $this->setMethod('post');
        $this->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
        
        $view = Zend_Layout::getMvcInstance()->getView();
        $questionMark = '<img src="' . $view->baseUrl() . '/images/question_mark.png" alt="vysvetlivky"/>';

        //dekorátory
       	$elementDecoratorColspan = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
       		array('Label', array('tag' => 'td')),
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
       	$firstHalf = new Zend_Form_SubForm();
       	$firstHalf->setName('firstHalf');
       	$firstHalf->setDecorators(array(
        	'FormElements',
        	array('HtmlTag', array('tag' => 'table')),
        	'Form',
        ));
       	$firstHalf->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
       
       	$firstHalf->addElement('hidden', 'id_workplace', array(
       		'decorators' => $elementDecorator,
       	));
       	
       	$firstHalf->addElement('hidden', 'id_factor', array(
       		'value' => 17,
       	));
       	      	
       	$firstHalf->addElement('text', 'name', array(
       		'label' => 'Název pracoviště',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 1,
       	));
       	
       	$firstHalf->addElement('textarea', 'description', array(
       		'label' => 'Popis pracoviště (jaké pracovní činnosti se zde vykonávají, technická zařízení a technologie...)',
       		'required' => true,
       		'filters' => array('StringTrim', 'StripTags'),
       		'decorators' => $elementDecoratorColspan,
       		'order' => 2,
       	));
       	
       	$firstHalf->addElement('hidden', 'workplaceFactors', array(
       		'label' => 'Faktory pracovního prostředí:',
       		'decorators' => $elementDecoratorColspan,
       		'order' => 3,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '1', array(
       		'factor' => 'Prach',
       		'factorLabel' => $questionMark,
       		'order' => 4,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '2', array(
       		'factor' => 'Chemické látky',
       		'factorLabel' => $questionMark,
       		'order' => 5,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '3', array(
       		'factor' => 'Hluk',
       		'factorLabel' => $questionMark,
       		'order' => 6,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '4', array(
       		'factor' => 'Vibrace',
       		'factorLabel' => $questionMark,
       		'order' => 7,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '5', array(
       		'factor' => 'Neionizující záření a elektromagnetická pole',
       		'factorLabel' => $questionMark,
       		'order' => 8,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '6', array(
       		'factor' => 'Fyzická zátěž',
       		'factorLabel' => $questionMark,
       		'order' => 9,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '7', array(
       		'factor' => 'Pracovní poloha',
       		'factorLabel' => $questionMark,
       		'order' => 10,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '8', array(
       		'factor' => 'Zátěž teplem',
       		'factorLabel' => $questionMark,
       		'order' => 11,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '9', array(
       		'factor' => 'Zátěž chladem',
       		'factorLabel' => $questionMark,
       		'order' => 12,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '10', array(
       		'factor' => 'Psychická zátěž',
       		'factorLabel' => $questionMark,
       		'order' => 13,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '11', array(
       		'factor' => 'Zraková zátěž',
       		'factorLabel' => $questionMark,
       		'order' => 14,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '12', array(
       		'factor' => 'Práce s biologickými činiteli',
       		'factorLabel' => $questionMark,
       		'order' => 15,
       	));
       	
       	$firstHalf->addElement('workplaceFactor', '13', array(
       		'factor' => 'Práce ve zvýšeném tlaku vzduchu',
       		'factorLabel' => $questionMark,
       		'order' => 16,
       	));
       	
       	$firstHalf->addElement('button', 'new_factor', array(
       		'label' => 'Další faktor',
       		'order' => 100,
       		'decorators' => $elementDecorator2,
       	));
       	
       	//polovina s riziky
       	$secondHalf = new Zend_Form_SubForm();
       	$secondHalf->setName('secondHalf');
       	$secondHalf->setDecorators(array(
        	'FormElements',
        	array('HtmlTag', array('tag' => 'table')),
        	'Form',
        ));
        $secondHalf->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
       	
		$secondHalf->addElement('hidden', 'id_risk', array(
       		'value' => 2,
       	));
        
        $secondHalf->addElement('hidden', 'mainRisks', array(
       		'label' => 'Rizika na pracovišti:',
       		'decorators' => $elementDecoratorColspan,
       		'order' => 101,
       	));
       	
       	$secondHalf->addElement('workplaceRisk', '1', array(
       		'order' => 102,
       	));
       	
       	$secondHalf->addElement('button', 'new_risk', array(
       		'label' => 'Další riziko',
       		'order' => 199,
       		'decorators' => $elementDecorator2,
       	));
       	
       	$secondHalf->addElement('submit', 'save', array(
       		'decorators' => $elementDecorator2,
       		'order' => 200,	
       	));
       	
       	$this->addSubForm($firstHalf, 'firstHalf');
       	$this->addSubForm($secondHalf, 'secondHalf');
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
    	$this->addElement($element, $name, array(
    		'order' => $order,
    	));
    }

}

