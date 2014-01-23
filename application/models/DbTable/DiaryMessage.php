<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DiaryMessage
 *
 * @author petr
 */
class Application_Model_DbTable_DiaryMessage extends Zend_Db_Table_Abstract {
    
    protected $_name = "diary_message";
    
    protected $_primary = "id";
    
    protected $_sequence = true;
    
    protected $_referenceMap = array(
        "client" => array(
            "columns" => array("client_id"),
            "refTableClass" => "Application_Model_DbTable_Client",
            "refColumns" => array("id_client")
        ),
        "subsidiary" => array(
            "columns" => array("subsidiary_id"),
            "refTableClass" => "Application_Model_DbTable_Subsidiary",
            "refColumns" => array("id_subsidiary")
        )
    );
    
    /**
     * vytvori novy zaznam v tabulce
     * 
     * @param int $clientId identifikacni cislo klienta
     * @param int $subsidiaryId identifikaceni cislo pobocky
     * @param string $text text zpravy
     * @return Zend_Db_Table_Row_Abstract
     */
    public function createMessage($clientId, $subsidiaryId, $text) {
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $retVal = $this->createRow(array(
            "client_id" => $clientId,
            "subsidiary_id" => $subsidiaryId,
            "author" => $user->name,
            "message" => $text,
            "is_out" => $user->role == My_Role::ROLE_CLIENT
        ));
        
        $retVal->save();
        
        return $retVal;
    }
    
    public function findById($id) {
        $select = $this->prepareSelect();
        $select->where("d.id = ?", $id);
        
        $rowClass = $this->_rowClass;
        
        return new $rowClass(array("data" => $select->query()->fetch(), "stored" => true, "table" => $this));
    }
    
    public function findMessages($showNew = 1, $showOld = 0) {
        $select = $this->prepareSelect();
        
        if (!$showNew) {
            $select->where("is_closed");
        }
        
        if (!$showOld) {
            $select->where("!is_closed");
        }
        
        $select->order("created_at desc");
        
        $data = $select->query()->fetchAll();
        $rowset = $this->_rowsetClass;
        
        return new $rowset(array("data" => $data, "stored" => true, "rowClass" => $this->_rowClass));
    }
    
    public function prepareSelect() {
        $select = new Zend_Db_Select($this->getAdapter());
        
        $select->from(array("d" => $this->_name));
        
        // napojeni na klienta a pobocku
        $select->joinInner(array("c" => "client"), "c.id_client = d.client_id", array("company_name"));
        $select->joinInner(array("s" => "subsidiary"), "s.id_subsidiary = d.subsidiary_id", array("subsidiary_town", "subsidiary_street"));
        
        return $select;
    }
}

?>
