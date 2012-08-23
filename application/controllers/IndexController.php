<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Index';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {          	
    	$diary = new Application_Model_DbTable_Diary();
    	$messages = $diary->getDiary();
    	$this->_helper->diary($messages);
		$this->_helper->diaryMessages();
    }


}



