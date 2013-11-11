<?php
class Zend_View_Helper_UserAdmin extends Zend_View_Helper_Abstract{
	
	public function userAdmin(){
		return '<div class="box"><a href="'
		. $this->view->url(array(), 'userRights')
		. '">Přidělit přístupová práva</a> | <a href="'
		. $this->view->url(array(), 'userRevoke')
		. '">Odebrat přístupová práva</a> | <a href="'
		. $this->view->url(array(), 'userRegister')
		. '">Vytvořit nového uživatele</a> | <a href="'
		. $this->view->url(array(), 'userDelete')
		. '">Smazat uživatele</a> | <a href="'
		. $this->view->url(array(), 'userList')
		. '">Seznam uživatelů</a></div>';
	}
}