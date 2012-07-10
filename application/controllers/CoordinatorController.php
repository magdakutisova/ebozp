<?php

class CoordinatorController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('coordinator');
        $this->view->title = 'Koordinátor';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {
        // action body
    }


}

