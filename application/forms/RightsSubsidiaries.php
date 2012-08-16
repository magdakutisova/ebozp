<?php

class Application_Form_RightsSubsidiaries extends Zend_Form
{

    public function init()
    {
        $this->setName('rightsSubsidiaries');
        
        $this->addElement('hidden', 'userId', array());
        
        $this->addElement('multicheckbox', 'subsidiaries', array(
        	'label' => 'Vyberte pobočky',
        	'required' => false,
        ));
        
        $this->addElement('submit', 'revoke', array(
        	'label' => 'Odebrat práva',
        ));
    }


}

