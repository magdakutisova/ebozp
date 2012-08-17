<?php
class My_Controller_Helper_DiaryFiltering extends Zend_Controller_Action_Helper_Abstract{
	
	private $view;
	
	public function __construct(){
		$this->view = Zend_Layout::getMvcInstance()->getView();
	}
	
	public function direct($messages, $userFilter, $subsidiaryFilter, $subs = false){
		
		//vyfiltrovat zprávy podle práv
		//vybrat z nich uživatele
		//vybrat z nich pobočky
		$acl = new My_Controller_Helper_Acl();
		if (Zend_Auth::getInstance()->hasIdentity()){
			$username = Zend_Auth::getInstance()->getIdentity()->username;
		}
		$usersDb = new Application_Model_DbTable_User();
		$user = $usersDb->getByUsername($username);
		$subsidiariesDb = new Application_Model_DbTable_Subsidiary();
		
		$userList = array();
		$userList[0] = '--Všichni autoři zpráv--';
		$subsidiaryList = array();
		$subsidiaryList[0] = '--Všechny pobočky--';
		
		foreach ($messages as $key => $message){
			$subsidiary = $subsidiariesDb->getSubsidiary($message->getSubsidiaryId(), true);
			if(!$acl->isAllowed($user, $subsidiary)){
				unset($messages[$key]);
				continue;
			}
			$userList[$message->getAuthor()] = $message->getAuthor();
			$sub = $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryTown();
			if ($subsidiary->getHq()){
				$sub .= ' (centrála)';
			}
			$subsidiaryList[$subsidiary->getIdSubsidiary()] = $sub;
			 
			if(($userFilter != strval(0) && $message->getAuthor() != $userFilter) || ($subsidiaryFilter != 0 && $message->getSubsidiaryId() != $subsidiaryFilter)){
				unset($messages[$key]);
			}
		}
		uasort($userList, 'strcoll');
		uasort($subsidiaryList, 'strcoll');

		//poslat zprávy do view->records
		$this->view->records = $messages;
		//poslat uživatele a pobočky do formuláře
		$form = new Application_Form_DiaryFilters();
		$form->users->setMultiOptions($userList);
		$form->users->setValue(array($userFilter));
		if($subs){
			$form->removeElement('subsidiaries');
		}
		else{
			$form->subsidiaries->setMultiOptions($subsidiaryList);
			$form->subsidiaries->setValue(array($subsidiaryFilter));
		}
		//poslat formulář do view->formFilter
		$this->view->formFilter = $form;
	}
	
}