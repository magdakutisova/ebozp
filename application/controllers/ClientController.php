<?php

class ClientController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Klient';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {
        // action body
    }

    public function searchAction()
    {
        // action body
    }

    public function listAction()
    {
        $clients = new Application_Model_DbTable_Client();
        $this->view->clients = $clients->fetchAll();
    }

    public function newAction()
    {
        // action body
    }


}







