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
		$helperButton = new Zend_View_Helper_FormButton();
		$helperButton->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= '<td><label for="' . $name . '[id_responsibility]">Odpovědnost</label><br/>' . $helperSelect->formSelect($name . '[id_responsibility]', $idResponsibility, null, $multiOptions);
		$this->html .= '<br/>' . $helperButton->formButton('new_responsibility', 'Nová odpovědnost') . '</td>';
		$this->html .= '<td colspan=2><label for="' . $name . '[id_employee]">Zaměstnanec</label><br/>' . $helperSelect->formSelect($name . '[id_employee]', $idEmployee, null, $multiOptions2);
		$this->html .= '<br/>' . $helperButton->formButton('new_employee', 'Nový zaměstnanec') . '</td>';
		$this->html .= '</tr>';

		return $this->html;
		
		
	}
	
}