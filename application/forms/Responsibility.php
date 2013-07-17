<?php
class Application_Form_Responsibility extends Zend_Form{
	
	public function init(){
		$this->setName('responsibility');
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
		
		$this->addElement('text', 'responsibility', array(
				'label' => 'Nová odpovědnost:',
				'required' => true,
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array(
								'validator' => 'StringLength',
								'options' => array(
										1, 255
										),
								),
						),
				));
		
		$this->addElement('hidden', 'rowId', array(
				));
		
		$this->addElement('button', 'save_responsibility', array(
				'decorators' => $elementDecorator2,
				'label' => 'Uložit odpovědnost',
				));
		
		$this->addElement('hidden', 'id_client', array());
	}
	
}