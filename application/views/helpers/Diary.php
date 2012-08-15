<?php
class Zend_View_Helper_Diary extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
	
	public function diary(){
		$messages = $this->view->records;
		$content = '';
		if ($messages != 0){
			$acl = new My_Controller_Helper_Acl();
			if(Zend_Auth::getInstance()->hasIdentity()){
				$username = Zend_Auth::getInstance()->getIdentity()->username;
			}
			$users = new Application_Model_DbTable_User();
			$user = $users->getByUsername($username);
			$subsidiariesDb = new Application_Model_DbTable_Subsidiary();
			
			foreach ($messages as $message){
				if(!$acl->isAllowed($user, $subsidiariesDb->getSubsidiary($message->getSubsidiaryId(), true))){
					continue;
				}
				$content .= '<p class="diary-message">'
					. '<span class="bold">' . $message->getDate() . '</span> '
					. $message->getMessage() . '</p>';
			}
		}
		if ($content == ''){
			$content = '<p class="diary-message">Nemáte žádné zprávy v bezpečnostním deníku.</p>';
		}
		//TODO jak dlouho uchovávat záznamy v bezpečnostním deníku
		return $content;
	}
	
}