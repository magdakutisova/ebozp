<?php

class Planning_TaskController extends Zend_Controller_Action {

    /**
     * vytvori novy ukol
     */
    public function postAction() {
        // vytovreni formulare a nstaveni hodnot
        $form = new Planning_Form_Item();
        $clientId = $this->_request->getParam("clientId");
        $subsidiaryId = $this->_request->getParam("subsidiaryId");

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                // zacatek transakce
                $tableItems = new Planning_Model_Items();
                $adapter = $tableItems->getAdapter();
                $adapter->beginTransaction();

                // vlozeni noveho ukolu
                $row = $tableItems->createRow($form->getValues(true));
                $row->created_by = Zend_Auth::getInstance()->getIdentity()->id_user;
                $row->client_id = $clientId;
                $row->subsidiary_id = $subsidiaryId;

                $row->save();

                // vyhodnoceni typu ukolu
                switch ($row->task_type) {
                case Planning_Model_Items::TASK_AUDIT:
                    break;

                case Planning_Model_Items::TASK_CHECK:
                    break;

                case Planning_Model_Items::TASK_WATCH:
                    break;

                default:
                    // nepodporovany typ ukolu
                    throw new Zend_Application_Exception("Unsupported task type");
                }

                // potvrzeni transakce a nastaveni radku do pohledu
                $adapter->commit();
                $this->view->row = $row;
            }
        }

        $this->view->form = $form;
        $this->view->clientId = $clientId;
        $this->view->subsidiaryId = $subsidiaryId;
    }

    /**
     * zobrazi formular pro upravu ukolu nebo upravi ukol
     */
    public function putAction() {

    }
}