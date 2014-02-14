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
        
        // nacteni jmenoviteho seznamu dohlidek a indexace dle id pobocky
        $tableWatches = new Audit_Model_Watches();
        $watches = $tableWatches->fetchAll(array(
            "client_id = ?" => $client->id_client,
            "watched_at > MAKEDATE(YEAR(NOW()), 1)",
            "is_closed"
        ), array("subsidiary_id", "watched_at"));
        
        $watchIndex = array();
        $lastId = 0;
        
        foreach ($watches as $item) {
            if ($lastId != $item->subsidiary_id) {
                $lastId = $item->subsidiary_id;
                $watchIndex[$lastId] = array();
            }
            
            $watchIndex[$lastId][] = $item;
        }
        
        $this->view->client = $client;
        $this->view->progress = $progress;
        $this->view->watchIndex = $watchIndex;
    }
    
    public function indexAction() {
        $tableClients = new Application_Model_DbTable_Client();
        $progress = $tableClients->getProgress();
        
        $this->view->progress = $progress;
    }
}

?>
