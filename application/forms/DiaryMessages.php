<?php

class Application_Form_DiaryMessages extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->addPrefixPath('My_Form_Element', 'My/Form/Element', 'Element');
        $this->setName('tree');
        
        $this->setDecorators(array(
    		'FormElements',
    		array('HtmlTag',array('tag' => 'div')),
    		'Form'
		));
        
        $inlineDecorator = array(
        	'ViewHelper',
        	array('Errors'),
        	array(array('row' => 'HtmlTag'), array('tag' => 'span')),
        	array('Label', 'placement' => 'PREPEND'),
        );
        
        $inlineDecoratorTree = array(
        	'ViewHelper',
        	array('Errors'),
        	array(array('row' => 'HtmlTag'), array('tag' => 'span', 'class' => 'tree')),
        	array('Label', 'placement' => 'PREPEND'),
        );
        
        $this->addElement('treeView', 'tree', array(
        	'label' => 'Adresář:',
        	'decorators' => $inlineDecoratorTree,
        		'order' => 1,
        ));
        
        $this->addElement('textarea', 'message', array(
        	'filters' => array('StripTags'),
        	'required' => true,
        	'decorators' => $inlineDecorator,
        		'order' => 2,
        ));
        
        $this->addElement('submit', 'send', array(
        	'label' => 'Odeslat',
        		'order' => 3,
        ));
    }


}

