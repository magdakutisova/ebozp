<?php
class Zend_View_Helper_Diary extends Zend_View_Helper_Abstract{
	
	public function diary(){
		$diary = new Application_Model_DbTable_Diary();
		$messages = $diary->getDiary();
		$content = '';
		if (count($messages) > 0){
			foreach ($messages as $message){
				$content .= '<p class="diary-message">'
					. '<span class="bold">' . $message->date . '</span> '
					. $message->message . '</p>';
			}
		}
		else{
			$content = '<p class="diary-message">Nemáte žádné zprávy v bezpečnostním deníku.</p>';
		}
		//TODO jak dlouho uchovávat záznamy v bezpečnostním deníku
		return $content;
	}
	
}