<?php
class My_Controller_Helper_DiaryRecord extends Zend_Controller_Action_Helper_Abstract{
	
	private $diary;
	private $diaryDb;
    
    private $_messages = array();
	
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
				 . $what;
            
            if (!is_null($anchor)) {
                $message .= ' ('
				 . $anchor . ').';
            }
		}
		else {
			$message = $who . ' '
				 . $what . ' (<a href="'
				 . Zend_Controller_Action_Helper_Url::url($urlParams, $route) . '">'
				 . $anchor . '</a>).';
		}
        
        $this->_messages[] = array(
            "msg" => $message,
            "subsidiaryId" => $subsidiary,
            "author" => $who
        );
        
	}
    
    public function save() {
        // pokud nejsou k dispozici zadne zpravy k ulozeni, nic se delat nebude
        if (!$this->_messages) return;
        
        $sql = "insert into " . $this->diaryDb->info("name") . " (`message`, `subsidiary_id`, `author`) values ";
        $adapter = Zend_Db_Table::getDefaultAdapter();
        
        $records = array();
        
        foreach ($this->_messages as $message) {
            $records[] = sprintf("(%s, %d, %s)", $adapter->quote($message["msg"]), $message["subsidiaryId"], $adapter->quote($message["author"]));
        }
        
        $sql .= implode(",", $records);
        $adapter->query($sql);
        
        $this->_messages = array();
    }
	
    public function postDispatch() {
        parent::postDispatch();
        
        $this->save();
    }
}