<?php
class Zend_View_Helper_FolderSwitch extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
	
	public function folderSwitch($workplaceId){
		$content = '';
		
		$form = new Application_Form_Select();
		
		$form->setDecorators(array(
        	'FormElements',
        	'Form',
        ));
        
        $form->setAttrib('class', 'inline');
		
		$form->select->setLabel('Přesunout do podadresáře:');
		$folders = new Application_Model_DbTable_Folder();
		$folderList = $folders->getFolders($this->view->clientId);
		$form->select->setMultiOptions($folderList);
		
		$form->submit->setLabel('Uložit');
		
		$form->addElement('hidden', 'workplace_id', array(
			'value' => $workplaceId,
		));
		
		$form->select->setDecorators(array('Label', 'ViewHelper'));
		$form->submit->setDecorators(array('ViewHelper'));
		$form->workplace_id->setDecorators(array('ViewHelper'));
		
		$content .= $form->__toString();
		return $content;
	}
	
}