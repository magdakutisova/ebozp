<?php
class Application_Form_User extends Application_Form_Register {
	
	public function init() {
		parent::init();
		
		$this->removeElement("username");
		$this->removeElement("password");
		$this->removeElement("confirmPassword");
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement("textarea", "cert_nums", array(
				"label" => "Čísla certifikátů",
				"required" => false,
				"decorators" => $elementDecorator,
				"order" => 2
				));
        
        $this->addElement("text", "elearning_user_login", array(
            "label" => "Přihlašovací jméno do elearningu",
            "required" => false,
            "decorators" => $elementDecorator,
            "order" => 3,
            "filters" => array(
                new Zend_Filter_Null()
            )
        ));
		
		$this->getElement("create")->setLabel("Uložit");
	}
}