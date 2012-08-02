<?php
interface Application_Model_UserOwnedInterface{
	
	public function isOwnedByUser(Application_Model_User $user);
	
}