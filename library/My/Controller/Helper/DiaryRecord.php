<?php
class My_Controller_Helper_DiaryRecord extends Zend_Controller_Action_Helper_Abstract{
	
	private $diary;
	private $diaryDb;
	
	public function __construct(){
		$this->diary = new Application_Model_Diary();
		$this->diaryDb = new Application_Model_DbTable_Diary();
	}
	
	public function direct($who, $what, $urlParams, $route, $anchor, $subsidiary){
		if($route == null){
			$message = $who . ' '
				 . $what . ' '
				 . $anchor . '.';
		}
		else {
			$message = $who . ' '
				 . $what . ' <a href="'
				 . Zend_Controller_Action_Helper_Url::url($urlParams, $route) . '">'
				 . $anchor . '</a>.';
		}		
		
		$this->diary->setMessage($message);
		$this->diary->setSubsidiaryId($subsidiary);
		$this->diary->setAuthor($who);
		$this->diaryDb->addMessage($this->diary);
	}
	
}