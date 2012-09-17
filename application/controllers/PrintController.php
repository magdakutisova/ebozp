<?php

class PrintController extends Zend_Controller_Action
{

    public function init()
    {
        //globální nastavení view
		$this->view->title = 'Záznamy k tisku';
		$this->view->headTitle ( $this->view->title );		
		$this->_helper->layout()->setLayout('clientLayout');
    }

    public function indexAction()
    {
        $this->view->subtitle = 'Záznamy k tisku';
        $this->view->clientId = $this->getRequest()->getParam('clientId');        
    }

    public function diaryAction()
    {
        $this->view->subtitle = 'Historie BD';
        $clientId = $this->getRequest()->getParam('clientId');
        
        $diary = new Application_Model_DbTable_Diary();
        $records = $diary->getDiaryByClient($clientId);
        
        $acl = new My_Controller_Helper_Acl();
        $subsidiaries = new Application_Model_DbTable_Subsidiary();
        
        $users = new Application_Model_DbTable_User();
        $user = $users->getByUsername(Zend_Auth::getInstance()->getIdentity()->username);
        foreach ($records as $key => $record){
        	if (!$acl->isAllowed($user, $subsidiaries->getSubsidiary($record->getSubsidiaryId()))){
        		unset($records[$key]);
        	} 
        }
        $this->view->records = $records;
    }


}



