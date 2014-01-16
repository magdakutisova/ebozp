<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Index';
        $this->view->headTitle($this->view->title);
    }
    
    public function aboutAction() {
        
    }
    
    public function contactsAction() {
        
    }
    
    public function helpAction() {
        
    }

    public function indexAction()
    {
    	$auth = Zend_Auth::getInstance()->getIdentity();
    	if($auth->role == My_Role::ROLE_CLIENT){
    		$userSubs = new Application_Model_DbTable_UserHasSubsidiary();
    		$this->view->subsidiaries = $userSubs->getByRoleAndUsername($auth->role, $auth->username);
    	}
    	
    	$diary = new Application_Model_DbTable_Diary();
    	if($auth->role == My_Role::ROLE_ADMIN || $auth->role == My_Role::ROLE_COORDINATOR){
    		$messages = $diary->getDiaryLastMonths(1);
    	}
    	else{
    		$messages = $diary->getDiaryLastMonths(12);
    	}
    	$this->_helper->diary($messages);
		$this->_helper->diaryMessages();
    }


}



