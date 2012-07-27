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
		$this->view->records = $diary->getDiary();
    }


}



