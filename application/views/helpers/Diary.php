<?php
class Zend_View_Helper_Diary extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
	
	public function diary(){
		$messages = $this->view->records;
		$content = '';
		if ($messages != 0){
			foreach ($messages as $message){
				$content .= '<p class="diary-message">'
					. '<span class="bold">' . $message->getDate() . '</span> '
					. $message->getMessage() . '</p>';
			}
		}
		else{
			$content = '<p class="diary-message">Nemáte žádné zprávy v bezpečnostním deníku.</p>';
		}
		//TODO jak dlouho uchovávat záznamy v bezpečnostním deníku
		return $content;
	}
	
}