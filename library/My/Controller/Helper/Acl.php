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
		$this->add(new Zend_Acl_Resource('utility'));
		$this->add(new Zend_Acl_Resource('work'));
		$this->add(new Zend_Acl_Resource('technical'));
		$this->add(new Zend_Acl_Resource('chemical'));
		$this->add(new Zend_Acl_Resource('employee'));
		
		/*
		 * ZDROJE MODULU AUDIT 
		 */
		$this->add(new Zend_Acl_Resource("audit:form"));
		$this->add(new Zend_Acl_Resource("audit:category"));
		$this->add(new Zend_Acl_Resource("audit:index"));
		$this->add(new Zend_Acl_Resource("audit:audit"));
		$this->add(new Zend_Acl_Resource("audit:mistake"));
		$this->add(new Zend_Acl_Resource("audit:check"));
		$this->add(new Zend_Acl_Resource("audit:workplace"));
		$this->add(new Zend_Acl_Resource("audit:report"));
		$this->add(new Zend_Acl_Resource("audit:section"));
		$this->add(new Zend_Acl_Resource("audit:question"));
		$this->add(new Zend_Acl_Resource("audit:watch"));
		
		/*
		 * ZDROJE MODULU DOCUMENT 
		 */
		$this->add(new Zend_Acl_Resource("document:index"));
		$this->add(new Zend_Acl_Resource("document:directory"));
		$this->add(new Zend_Acl_Resource("document:document"));
		$this->add(new Zend_Acl_Resource("document:documentation"));
		$this->add(new Zend_Acl_Resource("document:preset"));
		$this->add(new Zend_Acl_Resource("document:name"));
		
		/*
		 * ZDROJE MODULU DEADLINE
		 */
		$this->add(new Zend_Acl_Resource("deadline:index"));
		$this->add(new Zend_Acl_Resource("deadline:deadline"));
		$this->add(new Zend_Acl_Resource("deadline:log"));
		
		$preventist = My_Role::ROLE_PREVENTIST;
		$guest = My_Role::ROLE_GUEST;
		$client = My_Role::ROLE_CLIENT;
		$technician = My_Role::ROLE_TECHNICIAN;
		$coordinator = My_Role::ROLE_COORDINATOR;
		$admin = My_Role::ROLE_ADMIN;
		$superadmin = My_Role::ROLE_SUPERADMIN;
		
		$this->addRole(new Zend_Acl_Role($guest));
		$this->addRole(new Zend_Acl_Role($client));
		$this->addRole(new Zend_Acl_Role($preventist), $client);
		$this->addRole(new Zend_Acl_Role($technician), $client);
		$this->addRole(new Zend_Acl_Role($coordinator), $technician);
		$this->addRole(new Zend_Acl_Role($superadmin));
		$this->addRole(new Zend_Acl_Role($admin), $superadmin);
		
		$this->allow($guest, array('user', 'error'));
		$this->deny($guest, 'user', array('register', 'rights', 'delete', 'revoke'));
		
		$this->allow($client, array('index', 'client', 'subsidiary', 'user', 'error', 'workplace', 'position', 'print'));
		$this->allow($client, 'subs', null, new My_Controller_Helper_UserOwned());
		$this->allow($client, 'work');
		$this->allow($client, 'technical');
		$this->allow($client, 'chemical');
		$this->allow($client, 'employee');
		$this->allow($client, "audit:audit", array("list", "get"));
		$this->allow($client, "audit:mistake", array("get", "get.html", "index"));
		$this->allow($client, "audit:report", array("get", "preview.pdf"));
		$this->allow($client, "audit:form", array("get"));
		$this->allow($client, "document:directory");
		$this->allow($client, "document:document");
		$this->deny($client, "document:directory", array("editother", "showall", "post", "delete"));				// pomocne akce - editother umoznuje editovat cizi adresare a show all umoznuje pristup k cizim adresarum
		$this->deny($client, "document:document", array("editother", "showall"));					// pomocna akce - viz adresare
		$this->allow($client, "document:documentation", array("index"));
		$this->allow($client, "document:index");
		$this->deny($client, 'client', array('new', 'delete', 'list'));
		$this->deny($client, 'user', array('register', 'rights', 'delete', 'revoke'));
		$this->deny($client, 'subsidiary', array('new', 'delete'));
		$this->deny($client, 'workplace', array('new', 'edit', 'delete', 'newfolder', 'switchfolder', 'deletefolder'));
		$this->deny($client, 'position', array('new', 'edit', 'delete'));
		$this->deny($client, 'private');
		$this->deny($client, 'work', array('edit', 'delete'));
		$this->deny($client, 'technical', array('edit', 'delete'));
		$this->deny($client, 'chemical', array('edit', 'delete'));
		$this->deny($client, 'employee', array('edit', 'delete'));
		$this->allow($client, "deadline:index");
		$this->allow($client, "deadline:deadline", array("get", "submit"));
		$this->allow($client, "audit:watch", array("get", "index", "protocol.pdf"));
		
		$this->allow($technician, 'private');
		$this->allow($technician, 'client', 'list');
		$this->allow($technician, 'search');
		$this->allow($technician, 'workplace', array('new', 'edit', 'delete', 'newfolder', 'switchfolder', 'deletefolder'));
		$this->allow($technician, 'position', array('new', 'edit', 'delete'));
		$this->allow($technician, 'work', array('edit', 'delete'));
		$this->allow($technician, 'technical', array('edit', 'delete'));
		$this->allow($technician, 'chemical', array('edit', 'delete'));
		$this->allow($technician, 'employee', array('edit', 'delete'));
		$this->allow($technician, "audit:audit", array("index", "create", "clone", "post", "edit", "put", "get", "submit", "newcontact"));
		$this->allow($technician, "audit:mistake", array("attach", "detach", "edit.html", "get", "delete", "createalone1", "createalone2", "postalone", "edit", "delete.html", "delete", "put.html", "setstatus.json", "switch", "post", "create.html"));
		$this->allow($technician, "audit:form", array("instance", "fill", "save", "saveone.json"));
		$this->deny($technician, "audit:audit", array("clientlist"));
		$this->allow($technician, "audit:category", array("children.json"));
		$this->allow($technician, "audit:workplace", array("comment", "setplace", "post"));
		$this->allow($technician, "document:directory", array("editother", "showall", "index", "post", "delete"));
		$this->allow($technician, "document:document", array("editother"));
		$this->allow($technician, "audit:report",array("report.pdf", "create", "edit", "save"));
		$this->allow($technician, "audit:watch");
		$this->allow($technician, "deadline:deadline");
		$this->deny($technician, "deadline:deadline", "import");
		$this->allow($technician, "document:documentation");
		$this->deny($technician, "document:documentation", array("import", "reset"));
		
		$this->allow($coordinator, 'client', array('new', 'delete'));
		$this->allow($coordinator, 'subsidiary', array('new', 'delete'));
		$this->deny($coordinator, "audit:audit", array("fill", "post", "create"));
		$this->allow($coordinator, "audit:mistake", array("create", "post", "submit", "submit.json", "unsubmit", "unsubmit.json", "submits.json", "import"));
		$this->allow($coordinator, "document:preset");
		$this->allow($coordinator, "audit:form");
		
		$this->allow($superadmin);
		$this->deny($admin, 'utility');
		
	}
	
}
