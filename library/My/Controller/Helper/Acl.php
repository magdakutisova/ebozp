<?php
class My_Controller_Helper_Acl extends Zend_Acl{

	public function __construct(){
		
		$this->add(new Zend_Acl_Resource('index'));
		$this->add(new Zend_Acl_Resource('client'));
		$this->add(new Zend_Acl_Resource('search'));
		$this->add(new Zend_Acl_Resource('subsidiary'));
		$this->add(new Zend_Acl_Resource('user'));
		
		$guest = My_Role::ROLE_GUEST;
		$client = My_Role::ROLE_CLIENT;
		$technician = My_Role::ROLE_TECHNICIAN;
		$coordinator = My_Role::ROLE_COORDINATOR;
		$admin = My_Role::ROLE_ADMIN;
		
		$this->addRole(new Zend_Acl_Role($guest));
		$this->addRole(new Zend_Acl_Role($client), $guest);
		$this->addRole(new Zend_Acl_Role($technician), $client);
		$this->addRole(new Zend_Acl_Role($coordinator), $technician);
		$this->addRole(new Zend_Acl_Role($admin));
		
		$this->allow($guest, 'user');
		$this->deny($guest, 'user', 'register');
		$this->allow($client);
		$this->deny($client, 'user', 'register');
		$this->deny($client, 'client', 'new');
		$this->deny($client, 'client', 'delete');
		$this->allow($technician);
		$this->deny($technician, 'user', 'register');
		$this->deny($technician, 'client', 'new');
		$this->deny($technician, 'client', 'delete');
		$this->allow($coordinator);
		$this->deny($coordinator, 'user', 'register');
		$this->deny($coordinator, 'client', 'new');
		$this->deny($coordinator, 'client', 'delete');
		$this->allow($admin);
		
		//TODO pobočky by mohly jít řešit jako články k editaci
	}
	
}