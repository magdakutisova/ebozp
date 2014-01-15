<?php
class My_Controller_Helper_DiaryRecord extends Zend_Controller_Action_Helper_Abstract{
	
	private $diary;
	private $diaryDb;
	
	public function __construct(){
		$this->diary = new Application_Model_Diary();
		$this->diaryDb = new Application_Model_DbTable_Diary();
	}
    
    public function insertMessage($what, $urlParams, $route, $anchor, $subsidiary) {
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $username = $user->name;
        
        if (in_array($user->role, array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR, My_Role::ROLE_TECHNICIAN))) {
            $username .= " (G7)";
        } else {
            $username .= "(Klient)";
        }
        
        return $this->direct($username, $what, $urlParams, $route, $anchor, $subsidiary);
    }
	
	public function direct($who, $what, $urlParams, $route, $anchor, $subsidiary){
		if($route == null){
			$message = $who . ' '
				 . $what . ' ('
				 . $anchor . ').';
		}
		else {
			$message = $who . ' '
				 . $what . ' (<a href="'
				 . Zend_Controller_Action_Helper_Url::url($urlParams, $route) . '">'
				 . $anchor . '</a>).';
		}		
		
		$this->diary->setMessage($message);
		$this->diary->setSubsidiaryId($subsidiary);
		$this->diary->setAuthor($who);
		$this->diaryDb->addMessage($this->diary);
	}
	
}