<?php

class UserController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Uživatel';
		$this->view->headTitle ( $this->view->title );
    }

    public function indexAction()
    {
        // action body
    }

    public function registerAction()
    {
        $this->view->subtitle = 'Registrace';
        
        $form = new Application_Form_Register();
        $this->view->form = $form;
    }


}



