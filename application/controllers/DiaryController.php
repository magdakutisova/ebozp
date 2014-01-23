<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DiaryController
 *
 * @author petr
 */
class DiaryController extends Zend_Controller_Action {
    
    public function getAction() {
        $id = $this->_request->getParam("messageId");
        $tableMessages = new Application_Model_DbTable_DiaryMessage();
        $message = $tableMessages->findById($id);
        if (!$message) throw new Zend_Exception();
        
        $this->view->message = $message;
    }
    
    public function indexAction() {
        // nacteni pobocek, ke kterym ma uzivatel pristup
        $user = Zend_Auth::getInstance()->getIdentity();
        $tableMessages = new Application_Model_DbTable_DiaryMessage();
        
        // nacteni filtru
        $showNew = $this->_request->getParam("showNew", 1);
        $showOld = $this->_request->getParam("showOld", 0);
        
        $messages = $tableMessages->findMessages($showNew, $showOld);
        
        $this->view->messages = $messages;
        $this->view->showNew = $showNew;
        $this->view->showOld = $showOld;
    }
    
    public function sendAction() {
        $id = $this->_request->getParam("messageId");
        $tableMessages = new Application_Model_DbTable_DiaryMessage();
        $message = $tableMessages->findById($id);
        if (!$message) throw new Zend_Exception();
        
        $message->is_closed = true;
        $message->save();
        
        // odeslani zpravy do denniku, pokud je potreba
        $reply = trim($this->_request->getParam("reply"));
        
        if ($reply) {
            $diary = new Application_Model_DbTable_Diary();
            $name = Zend_Auth::getInstance()->getIdentity()->name;
            
            $toSave = new Application_Model_Diary();
            $toSave->setMessage($name . ' zaslal tuto zprávu: "' . $reply . '"');
            $toSave->setSubsidiaryId($message->subsidiary_id);
            $toSave->setAuthor($name);
            $diary->addMessage($toSave);
        }
        
        $this->view->message = $message;
    }
}

?>
