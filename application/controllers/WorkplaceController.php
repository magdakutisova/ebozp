<?php

class WorkplaceController extends Zend_Controller_Action
{

    public function init()
    {
    	//globální nastavení view
        $this->view->title = 'Pracoviště';
        $this->view->headTitle($this->view->title);
        $this->_helper->layout()->setLayout('clientLayout');
        
        //nastavit přístupová práva
    }

    public function indexAction()
    {
        // action body
    }

    public function newAction()
    {
        // action body
    }


}



