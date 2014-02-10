<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author petr
 */
class Planning_IndexController extends Zend_Controller_Action {
    
    public function init() {
        $this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
    }
    
    public function clientAction() {
        // nactnei klienta
        $tableClients = new Application_Model_DbTable_Client();
        $client = $tableClients->find($this->_request->getParam("clientId"))->current();
        
        // nacteni pobocek
        $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
        $progress = $tableSubsidiaries->getProgress($this->_request->getParam("clientId"));
        
        $this->view->client = $client;
        $this->view->progress = $progress;
    }
    
    public function indexAction() {
        $tableClients = new Application_Model_DbTable_Client();
        $progress = $tableClients->getProgress();
        
        $this->view->progress = $progress;
    }
}

?>
