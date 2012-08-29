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
        
        $this->setDecorators(array(
        	'FormElements',
        	array('HtmlTag', array('tag' => 'table')),
        	'Form',
        ));
                    	
       	$elementDecoratorColspan = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
       		array('Label', array('tag' => 'td')),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	
       	$elementDecorator2 = array(
       		'ViewHelper',
       		array('Errors'),
       		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
       		array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       	);
       	
       	$this->addElement('hidden', 'id_workplace', array());
       	$this->addElement('hidden', 'id_factor', array(
       		'value' => 17,
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
       	
       	$this->addElement('hidden', 'workplaceFactors', array(
       		'label' => 'Faktory pracovního prostředí:',
       		'decorators' => $elementDecoratorColspan,
       		'order' => 3,
       	));
       	
       	$this->addElement('workplaceFactor', '1', array(
       		'factor' => 'Prach',
       		'factorLabel' => $questionMark,
       		'order' => 4,
       	));
       	
       	$this->addElement('workplaceFactor', '2', array(
       		'factor' => 'Chemické látky',
       		'factorLabel' => $questionMark,
       		'order' => 5,
       	));
       	
       	$this->addElement('workplaceFactor', '3', array(
       		'factor' => 'Hluk',
       		'factorLabel' => $questionMark,
       		'order' => 6,
       	));
       	
       	$this->addElement('workplaceFactor', '4', array(
       		'factor' => 'Vibrace',
       		'factorLabel' => $questionMark,
       		'order' => 7,
       	));
       	
       	$this->addElement('workplaceFactor', '5', array(
       		'factor' => 'Neionizující záření a elektromagnetická pole',
       		'factorLabel' => $questionMark,
       		'order' => 8,
       	));
       	
       	$this->addElement('workplaceFactor', '6', array(
       		'factor' => 'Fyzická zátěž',
       		'factorLabel' => $questionMark,
       		'order' => 9,
       	));
       	
       	$this->addElement('workplaceFactor', '7', array(
       		'factor' => 'Pracovní poloha',
       		'factorLabel' => $questionMark,
       		'order' => 10,
       	));
       	
       	$this->addElement('workplaceFactor', '8', array(
       		'factor' => 'Zátěž teplem',
       		'factorLabel' => $questionMark,
       		'order' => 11,
       	));
       	
       	$this->addElement('workplaceFactor', '9', array(
       		'factor' => 'Zátěž chladem',
       		'factorLabel' => $questionMark,
       		'order' => 12,
       	));
       	
       	$this->addElement('workplaceFactor', '10', array(
       		'factor' => 'Psychická zátěž',
       		'factorLabel' => $questionMark,
       		'order' => 13,
       	));
       	
       	$this->addElement('workplaceFactor', '11', array(
       		'factor' => 'Zraková zátěž',
       		'factorLabel' => $questionMark,
       		'order' => 14,
       	));
       	
       	$this->addElement('workplaceFactor', '12', array(
       		'factor' => 'Práce s biologickými činiteli',
       		'factorLabel' => $questionMark,
       		'order' => 15,
       	));
       	
       	$this->addElement('workplaceFactor', '13', array(
       		'factor' => 'Práce ve zvýšeném tlaku vzduchu',
       		'factorLabel' => $questionMark,
       		'order' => 16,
       	));
       	
       	$this->addElement('button', 'new_factor', array(
       		'label' => 'Další faktor',
       		'order' => 100,
       		'decorators' => $elementDecorator2,
       	));
       	
       	$this->addElement('submit', 'save', array(
       		'decorators' => $elementDecorator2,
       		'order' => 200,	
       	));
    }

    public function preValidation(array $data){
    	function findFields($field){
    		if(strpos($field, 'newFactor') !== false){
    			return $field;
    		}
    	}
    	
    	$newFields = array_filter(array_keys($data), 'findFields');
    	
    	foreach($newFields as $fieldName){
    		$order = ltrim($fieldName, 'newFactor') + 1;
    		$this->addNewField($fieldName, $order);
    	}
    }
    
    public function addNewField($name, $order){
    	$this->addElement('workplaceFactor', $name, array(
    		'order' => $order,
    	));
    }

}

