<?php
class Zend_View_Helper_ClientInfo extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
	
	public function clientInfo(){
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$params = $request->getParams();
		
		$info = '';
		if(isset($params['clientId'])){
			$clients = new Application_Model_DbTable_Client();
			$client = $clients->getClient($params['clientId']);
			$info .= '<a href="' . $this->view->url(array('clientId' => $params['clientId']), 'clientIndex') . '">' . $client->getCompanyName() . '</a>';
			if($client->getArchived()){
				$info .=  ' (archivováno) ';
			}
		}
		if(isset($params['subsidiary'])){
			$subsidiaries = new Application_Model_DbTable_Subsidiary();
			$subsidiary = $subsidiaries->getSubsidiary($params['subsidiary']);
            
            if (!$subsidiary->getHq()) {
                $info .= ', Pobočka: <a href="' . $this->view->url(array('clientId' => $params['clientId'], 'subsidiary' => $params['subsidiary']), 'subsidiaryIndex') . '">' . $subsidiary->getSubsidiaryStreet() . ', ' . $subsidiary->getSubsidiaryTown() . '</a>';
                if(!$subsidiary->getActive()){
                    $info .= ' (neaktivní pobočka) ';
                }
            }
		}
		if(isset($params['subsidiaryId'])){
			$subsidiaries = new Application_Model_DbTable_Subsidiary();
			$subsidiary = $subsidiaries->getSubsidiary($params['subsidiaryId']);
			
            if (!$subsidiary->getHq()) {
                $info .= ', Pobočka: <a href="' . $this->view->url(array('clientId' => $params['clientId'], 'subsidiary' => $params['subsidiary']), 'subsidiaryIndex') . '">' . $subsidiary->getSubsidiaryStreet() . ', ' . $subsidiary->getSubsidiaryTown() . '</a>';
                if(!$subsidiary->getActive()){
                    $info .= ' (neaktivní pobočka) ';
                }
            }
		}
		return $info;
	}
	
}