<?php
class My_Controller_Helper_AddressBook extends Zend_Controller_Action_Helper_Abstract{
	
	private $view;
	private $controllerName;
	private $currentClient;
	private $currentSubsidiary;
	
	public function __construct(){
		$this->view = Zend_Layout::getMvcInstance()->getView();
		$this->controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		if($this->controllerName == 'client'){
			$this->currentClient = Zend_Controller_Front::getInstance()->getRequest()->getParam('clientId');
		}
		if($this->controllerName == 'subsidiary'){
			$this->currentSubsidiary = Zend_Controller_Front::getInstance()->getRequest()->getParam('subsidiary');
		}
	}
	
	public function direct(){
		$subsidiariesDb = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $subsidiariesDb->getByClient();
		
		$addressBook = array();
		
		$username = Zend_Auth::getInstance()->getIdentity()->username;
		$users = new Application_Model_DbTable_User();
		$user = $users->getByUsername($username);
		$acl = new My_Controller_Helper_Acl();
		
		foreach ($subsidiaries as $subsidiary){
			if(($this->controllerName == 'client' && $this->currentClient != $subsidiary->getClientId())
				|| ($this->controllerName == 'subsidiary' && $this->currentSubsidiary != $subsidiary->getIdSubsidiary())){
					continue;
				}
			if ($acl->isAllowed($user, $subsidiary)){
				if ($this->controllerName == 'subsidiary'){
					$addressBook[$subsidiary->getIdSubsidiary()]['title'] = $subsidiary->getSubsidiaryName();
					$addressBook[$subsidiary->getIdSubsidiary()]['children'] = array();
				}
				else{
					$addressBook['0']['title'] = "Vybrat vše";
					$addressBook['0']['children'] = array();
					$parentNode = "";
		
					if($subsidiary->getHq()){
						$parentNode = $subsidiary->getIdSubsidiary();
						$addressBook[$parentNode]['title'] = $subsidiary->getSubsidiaryName();
					}
					else{
						$addressBook[$parentNode]['children'][$subsidiary->getIdSubsidiary()]['title'] = $subsidiary->getSubsidiaryName();
						$addressBook[$parentNode]['children'][$subsidiary->getIdSubsidiary()]['children'] = array();
					}
				}
			}
		}
		
		return $addressBook;
	}	
	
}