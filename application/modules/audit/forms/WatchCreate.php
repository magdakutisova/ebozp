<?php
class Audit_Form_WatchCreate extends Audit_Form_Watch {
	
	public function init() {
		parent::init();
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
		
		$this->addElement("checkbox", "also_audit", array(
				"label" => "Zároveň se provádí audit/prověrka",
				"decorators" => $elementDecorator,
				"order" => 8
				));
	}
}