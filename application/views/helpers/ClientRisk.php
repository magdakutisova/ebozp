<?php

class Zend_View_Helper_ClientRisk extends Zend_View_Helper_Abstract {
    
    public function clientRisk() {
        // nacteni dat
        $request = Zend_Controller_Front::getInstance()->getRequest();
       
        $clientId = $request->getParam("clientId");
        
        $subsidiaryId = $request->getParam("subsidiary");
        
        if (is_null($subsidiaryId))
            $subsidiaryId = $request->getParam("subsidiaryId");
        
        $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
        
        // pokud neni nastaveno ani subsidiaryId ani clientId, vraci se NULL
        if (is_null($clientId) && is_null($subsidiaryId)) return null;
        
        if (is_null($subsidiaryId)) {
            $subsidiary = $tableSubsidiaries->fetchRow(array(
                "client_id = ?" => $clientId,
                "hq"
            ));
            
            $subsidiaryId = $subsidiary->id_subsidiary;
        }
        
        // nalezeni skore
        $tableMistakes = new Audit_Model_AuditsRecordsMistakes();
        
        return $tableMistakes->getScore($subsidiaryId);
    }
}