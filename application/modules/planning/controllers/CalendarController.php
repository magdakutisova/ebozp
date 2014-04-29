<?php

class Planning_CalendarController extends Zend_Controller_Action {
    
    public function indexAction() {
        // nacteni seznamu uzivatelu, kteri maji zaznam v kalendari
        $tableItems = new Planning_Model_Items();
        $adapter = $tableItems->getAdapter();
        
        // nacteni dat
        $users = $tableItems->getUsers();
        $tasks = $tableItems->getItems();

        $this->view->users = $users;
        $this->view->tasks = $tasks;
    }
}