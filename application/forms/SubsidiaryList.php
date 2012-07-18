<?php

class Application_Form_SubsidiaryList extends Zend_Form
{

    public function init()
    {
        $this->setName('subsidiaryList');
        
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
        
        $this->addElement('select', 'subsidiary', array(
        	'label' => 'Vyberte pobočku:',
        	'decorators' => $elementDecorator,
        ));
        
        $this->addElement('submit', 'edit', array(
        	'label' => 'Editovat',
        	'decorators' => $elementDecorator2,
        ));
        
        $this->addElement('submit', 'delete', array(
        	'label' => 'Smazat',
        	'attribs' => array('onClick' => 'return confirm("Opravdu chcete pobočku smazat?")'),
        	'decorators' => $elementDecorator2,
        ));
    }


}

