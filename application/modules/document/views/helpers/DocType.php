<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DocType
 *
 * @author petr
 */
class Document_View_Helper_DocType extends Zend_View_Helper_Abstract {
    
    public function docType() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $paramName = Document_DocumentationController::REQ_PARAM;
        
        $type = $request->getParam($paramName);
        
        $pattern = "<script type='text/javascript'>var %s = '%s'</script>";
        
        return sprintf($pattern, $paramName, $type);
    }
}

?>
