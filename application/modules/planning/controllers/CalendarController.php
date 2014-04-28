<?php

class Planning_CalendarController extends Zend_Controller_Action {
    
    public function indexAction() {
        // nacteni seznamu uzivatelu, kteri maji zaznam v kalendari
        $tableItems = new Planning_Model_Items();
        $adapter = $tableItems->getAdapter();
        
        $select = new Zend_Db_Select($adapter);
        $select->from(array("t" => $tableItems->info("name")));
        $select->group("t.user_id");

        // propojeni na uzivatele
        $tableUsers = new Application_Model_DbTable_User();
        $select->joinLeft(array("u" => $tableUsers->info("name")), "u.id_user = t.user_id", array("name"));

        $users = $select->query()->fetchAll();

        $this->view->users = $users;
    }
}