<?php
class My_Controller_Helper_Acl extends Zend_Acl{

	public function __construct(){
		
		$this->add(new Zend_Acl_Resource('index'));
		$this->add(new Zend_Acl_Resource('client'));
		$this->add(new Zend_Acl_Resource('search'));
		$this->add(new Zend_Acl_Resource('subsidiary'));
		$this->add(new Zend_Acl_Resource('user'));
		$this->add(new Zend_Acl_Resource('error'));
		$this->add(new Zend_Acl_Resource('subs'));
		$this->add(new Zend_Acl_Resource('private'));
		$this->add(new Zend_Acl_Resource('workplace'));
		$this->add(new Zend_Acl_Resource('position'));
		$this->add(new Zend_Acl_Resource('print'));
		
		/*
		 * ZDROJE MODULU AUDIT 
		 */
		$this->add(new Zend_Acl_Resource("audit:form"));
		$this->add(new Zend_Acl_Resource("audit:category"));
		$this->add(new Zend_Acl_Resource("audit:index"));
		$this->add(new Zend_Acl_Resource("audit:audit"));
		$this->add(new Zend_Acl_Resource("audit:mistake"));
		$this->add(new Zend_Acl_Resource("audit:check"));
		
		$guest = My_Role::ROLE_GUEST;
		$client = My_Role::ROLE_CLIENT;
		$technician = My_Role::ROLE_TECHNICIAN;
		$coordinator = My_Role::ROLE_COORDINATOR;
		$admin = My_Role::ROLE_ADMIN;
		
		$this->addRole(new Zend_Acl_Role($guest));
		$this->addRole(new Zend_Acl_Role($client));
		$this->addRole(new Zend_Acl_Role($technician), $client);
		$this->addRole(new Zend_Acl_Role($coordinator), $technician);
		$this->addRole(new Zend_Acl_Role($admin));
		
		$this->allow($guest, array('user', 'error'));
		$this->deny($guest, 'user', array('register', 'rights', 'delete', 'revoke'));
		
		$this->allow($client, array('index', 'client', 'subsidiary', 'user', 'error', 'workplace', 'print'));
		$this->allow($client, 'subs', null, new My_Controller_Helper_UserOwned());
		$this->allow($client, "audit:audit", array("list", "get"));
		$this->allow($client, "audit:mistake", array("get", "get.html", "index"));
		$this->allow($client, "audit:form", array("get"));
		$this->deny($client, 'client', array('new', 'delete', 'list'));
		$this->deny($client, 'user', array('register', 'rights', 'delete', 'revoke'));
		$this->deny($client, 'subsidiary', array('new', 'delete'));
		$this->deny($client, 'private');
		
		$this->allow($technician, 'private');
		$this->allow($technician, 'client', 'list');
		$this->allow($technician, 'search');
		$this->allow($technician, "audit:audit", array("index", "create", "clone", "post", "edit", "put", "get", "submit"));
		$this->allow($technician, "audit:mistake", array("attach", "edit.html", "get", "delete", "createalone1", "createalone2", "postalone", "edit", "delete.html", "delete", "put.html", "setstatus.json"));
		$this->allow($technician, "audit:form", array("instance", "fill", "save"));
		$this->deny($technician, "audit:audit", array("clientlist"));
		$this->allow($technician, "audit:category", array("children.json"));
		
		$this->allow($coordinator, 'client', array('new', 'delete'));
		$this->allow($coordinator, 'subsidiary', array('new', 'delete'));
		$this->deny($coordinator, "audit:audit", array("fill", "post", "create"));
		$this->allow($coordinator, "audit:mistake", array("create", "post", "submit", "submit.json", "unsubmit", "unsubmit.json", "submits.json"));
		
		$this->allow($admin);
		
	}
	
}
