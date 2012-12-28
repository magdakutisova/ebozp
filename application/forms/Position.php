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
		
		//dekorátory - doplnit
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
		);
		
		$elementDecoratorColspan = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 4)),
				array('Description', array('tag' => 'td')),
				array(array('closeTd' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'placement' => 'prepend')),
				array('Label', array()),
				array(array('openTd' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 1)),
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
        	'order' => 1000,
        ));
       	
       	$this->addElement('hidden', 'id_position', array(
       		'decorators' => $elementDecorator,
       		'order' => 1001,
       	));
       	
       	$this->addElement('select', 'subsidiary_id', array(
       			'label' => 'Pobočka',
       			'required' => true,
       			'decorators' => $elementDecoratorColspan,
       			'order' => 1,
       	));
       	
       	$this->addElement('text', 'position', array(
       			'label' => 'Název pracovní pozice',
       			'required' => true,
       			'filters' => array('StringTrim', 'StripTags'),
       			'decorators' => $elementDecoratorColspan,
       			'order' => 2,
       			'description' => $questionMarkStart . 'Uveďte název uvedený v pracovní smlouvě' . $questionMarkEnd,
       	));
       	$this->getElement('position')->getDecorator('Description')->setEscape(false);
	}
	
}