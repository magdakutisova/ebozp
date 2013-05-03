<?php
class Application_Form_Folder extends Zend_Form{
	
	public function init(){
		$this->setName('folder');
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
		
		$this->addElement('text', 'folder', array(
				'label' => 'Nové umístění',
				'required' => true,
				'filters' => array('StripTags', 'StringTrim'),
				'decorators' => $elementDecorator,
				'validators' => array(
						array('validator' => 'StringLength',
								'options' => array(1,255)),
				),
				));
		
		$this->addElement('button', 'save_folder', array(
				'decorators' => $elementDecorator2,
				'label' => 'Uložit umístění',
				));
		
		$this->addElement('hidden', 'clientId', array(
		));

	}
	
}