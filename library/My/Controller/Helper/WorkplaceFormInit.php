<?php
class My_Controller_Helper_WorkplaceFormInit extends Zend_Controller_Action_Helper_Abstract{
	
	public function direct(){
		$form = new Application_Form_Workplace();
		
		$form->addElement('hidden', 'id_factor', array(
       		'value' => 20,
       	));
       	
       	$form->addElement('workplaceFactor', 'factor7', array(
       		'factor' => 'Prach',
       		'order' => 7,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor8', array(
       		'factor' => 'Chemické látky',
       		'order' => 8,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor9', array(
       		'factor' => 'Hluk',
       		'order' => 9,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor10', array(
       		'factor' => 'Vibrace',
       		'order' => 10,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor11', array(
       		'factor' => 'Neionizující záření a elektromagnetická pole',
       		'order' => 11,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor12', array(
       		'factor' => 'Fyzická zátěž',
       		'order' => 12,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor13', array(
       		'factor' => 'Pracovní poloha',
       		'order' => 13,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor14', array(
       		'factor' => 'Zátěž teplem',
       		'order' => 14,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor15', array(
       		'factor' => 'Zátěž chladem',
       		'order' => 15,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor16', array(
       		'factor' => 'Psychická zátěž',
       		'order' => 16,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor17', array(
       		'factor' => 'Zraková zátěž',
       		'order' => 17,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor18', array(
       		'factor' => 'Práce s biologickými činiteli',
       		'order' => 18,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));
       	
       	$form->addElement('workplaceFactor', 'factor19', array(
       		'factor' => 'Práce ve zvýšeném tlaku vzduchu',
       		'order' => 19,
       		'validators' => array(new My_Validate_WorkplaceFactor()),
       	));	
		
		//rizika
		$form->addElement('hidden', 'id_risk', array(
       		'value' => 103,
       	));
		
		$form->addElement('workplaceRisk', 'risk102', array(
       		'order' => 102,
       		'validators' => array(new My_Validate_WorkplaceRisk()),
       	));
		
		$form->save->setLabel('Uložit');
		
		return $form;
	}
	
}