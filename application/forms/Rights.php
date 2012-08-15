<?php

class Application_Form_Rights extends Zend_Form
{

    public function init()
    {
        $users = new Zend_Form_SubForm();
        $users->setAttrib('class', 'span-8');
        
        $usersDb = new Application_Model_DbTable_User();
        $userList = $usersDb->getUsernames();
        
        $users->addElement('multiCheckbox', 'userCheckboxes', array(
        	'label' => 'Vyberte uživatele:',
        	'required' => true,
        	'multiOptions' => $userList,
        ));
        
        $subsidiariesDb = new Application_Model_DbTable_Subsidiary();
        $subsidiaryList = $subsidiariesDb->getSubsidiaries();
        
        $subsidiaries = new Zend_Form_SubForm();
        $subsidiaries->addElement('multiCheckbox', 'subsidiaryCheckboxes', array(
        	'label' => 'Vyberte pobočky:',
        	'required' => true,
       		'multiOptions' => $subsidiaryList,
        ));
        
        $this->addSubForms(array(
        	'users' => $users,
        	'subsidiaries' => $subsidiaries,
        ));
        
        $this->addElement('submit', 'grant', array(
        	'label' => 'Přidat práva',
        ));
    }


}

