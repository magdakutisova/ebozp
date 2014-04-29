<?php

class Planning_SubsidiaryController extends Zend_Controller_Action {

    public function indexAction() {
        // nacteni id pobocky, klienta a nacteni dat z databaze
        $subsidiaryId = $this->_request->getParam("subsidiaryId");
        $clientId = $this->_request->getParam("clientId");

        $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
        $subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();

        if (!$subsidiary) throw new Zend_Db_Exception("Subsidiary not found");

        // formular pro vytvoreni nove polozky
        $createForm = new Planning_Form_Item();
        $createForm->setUsersFromTable();

        // nastaveni routy
        $url = $this->view->url($this->_request->getParams(), "planning-task-post");
        $createForm->setAction($url);

        // nacteni ukolu pro pobocku
        $tableItems = new Planning_Model_Items();
        $items = $tableItems->findBySubsidiary($subsidiaryId);

        $this->view->subsidiary = $subsidiary;
        $this->view->createForm = $createForm;
        $this->view->items = $items;
    }
}