<?php
class My_View_Helper_CurrentEmployee extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function currentEmployee($name, $value = null, $attribs = null){
		$this->html = '';
		$idEmployee = $fullName = '';
		
		$multiOptions = isset($attribs['multiOptions']) ? $attribs['multiOptions'] : null;
		
		if($value){
			$idEmployee = $value['id_employee'];
			$fullName = $value['full_name'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_employee]', $idEmployee);
		$this->html .= '<td><label for="' . $name . '[full_name]">Vyberte zaměstnance:</label><td>';
		$this->html .= '<td colspan="4">' . $helperSelect->formSelect($name . '[full_name]', $fullName, null, $multiOptions) . '</td>';
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}