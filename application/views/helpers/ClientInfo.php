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
			$info .= 'Klient: <a href="' . $this->view->url(array('clientId' => $params['clientId']), 'clientIndex') . '">' . $client->getCompanyName() . '</a>';
		}
		if(isset($params['subsidiary'])){
			$subsidiaries = new Application_Model_DbTable_Subsidiary();
			$subsidiary = $subsidiaries->getSubsidiary($params['subsidiary']);
			$info .= ', Pobočka: <a href="' . $this->view->url(array('clientId' => $params['clientId'], 'subsidiary' => $params['subsidiary']), 'subsidiaryIndex') . '">' . $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryStreet() . ', ' . $subsidiary->getSubsidiaryTown() . '</a>';
		}
		if(isset($params['subsidiaryId'])){
			$subsidiaries = new Application_Model_DbTable_Subsidiary();
			$subsidiary = $subsidiaries->getSubsidiary($params['subsidiaryId']);
			$info .= ', Pobočka: <a href="' . $this->view->url(array('clientId' => $params['clientId'], 'subsidiary' => $params['subsidiaryId']), 'subsidiaryIndex') . '">' . $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryStreet() . ', ' . $subsidiary->getSubsidiaryTown() . '</a>';
		}
		return $info;
	}
	
}