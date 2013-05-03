<?php
class Application_Form_TechnicalDevice extends Zend_Form{
	
	public function init(){
		$this->setName('technicaldevice');
		$this->setMethod('post');
		
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form',
		));
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$elementDecorator2 = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement('text', 'sort', array(
				'label' => 'Druh technického prostředku',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(1,200)),
				),
				));
		
		$this->addElement('text', 'type', array(
				'label' => 'Typ technického prostředku',
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(0,200)),
				),
				));
		
		$this->addElement('button', 'save_technicaldevice', array(
				'decorators' => $elementDecorator2,
				'label' => 'Uložit technický prostředek',
				));
		
		$this->addElement('hidden', 'clientId', array(
				));
	}
	
}