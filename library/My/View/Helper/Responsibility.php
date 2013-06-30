<?php
class My_View_Helper_Responsibility extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function responsibility($name, $value = null, $attribs = null){
		$this->html = '';
		$idResponsibility = $idEmployee = '';
		
		$multiOptions = isset($attribs['multiOptions']) ? $attribs['multiOptions'] : null;
		$multiOptions2 = isset($attribs['multiOptions2']) ? $attribs['multiOptions2'] : null;
		
		if($value){
			$idResponsibility = $value['id_responsibility'];
			$idEmployee = $value['id_employee'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
	}
	
}