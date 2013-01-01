<?php
class My_View_Helper_Employee extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function employee($name, $value = null, $attribs = null){
		$this->html = '';
		$idEmployee = $title1 = $firstName = $surname = $title2 = $manager = $sex = $yearOfBirth = '';
		if(isset($attribs['multiOptions'])){
			$multiOptions = $attribs['multiOptions'];
		}
		else{
			$multiOptions = null;
		}
		if(isset($attribs['multiOptions2'])){
			$multiOptions2 = $attribs['multiOptions2'];
		}
		else{
			$multiOptions2 = null;
		}
		if(isset($attribs['multiOptions3'])){
			$multiOptions3 = $attribs['multiOptions3'];
		}
		else{
			$multiOptions3 = null;
		}
		if(isset($attribs['canViewPrivate'])){
			$canViewPrivate = $attribs['canViewPrivate'];
		}
		else{
			$canViewPrivate = false;
		}
		
		if($value){
			$idEmployee = $value['id_employee'];
			$title1 = $value['title_1'];
			$firstName = $value['first_name'];
			$surname = $value['surname'];
			$title2 = $value['title_2'];
			$manager = $value['manager'];
			$sex = $value['sex'];
			$yearOfBirth = $value['year_of_birth'];
			$note = $value['note'];
			$private = $value['private'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_employee]', $idEmployee);
		$this->html .= '<td><label for="' . $name . '[title_1]">Titul před jménem</label></td>';
		$this->html .= '<td><label for="' . $name . '[first_name]">Jméno</label></td>';
		$this->html .= '<td><label for="' . $name . '[surname]">Příjmení</label></td>';
		$this->html .= '<td><label for="' . $name . '[title_2]">Titul za jménem</label></td>';
		$this->html .= '<td><label for="' . $name . '[manager]">Vedoucí</label></td>';
		$this->html .= '<td><label for="' . $name . '[sex]">Pohlaví</label></td>';
		$this->html .= '<td><label for="' . $name . '[year_of_birth]">Rok narození</label></td>';
		$this->html .= '</tr><tr>';
		$this->html .= '<td>' . $helperText->formText($name . '[title_1]', $title1) . '</td>';
		$this->html .= '<td>' . $helperText->formText($name . '[first_name]', $firstName) . '</td>';
		$this->html .= '<td>' . $helperText->formText($name . '[surname]', $surname) . '</td>';
		$this->html .= '<td>' . $helperText->formText($name . '[title_2]', $title2) . '</td>';
		$this->html .= '<td>' . $helperSelect->formSelect($name . '[manager]', $manager, null, $multiOptions) . '</td>';
		$this->html .= '<td>' . $helperSelect->formSelect($name . '[sex]', $sex, null, $multiOptions2) . '</td>';
		$this->html .= '<td>' . $helperSelect->formSelect($name . '[year_of_birth]', $yearOfBirth, null, $multiOptions3) . '</td>';
		$this->html .= '</td></tr><tr>';
		$this->html .= '<td colspan="2"><label for="' . $name . '[note]">Poznámka k zaměstnanci</label>' . $helperText->formText($name . '[note]', $note) . '</td>';
		if($canViewPrivate){
			$this->html .= '<td colspan="4"><label for="' . $name . '[private]">Soukromá poznámka k zaměstnanci</label>' . $helperText->formText($name . '[private]', $private) . '</td><td></td>';
		}
		else{
			$this->html .= '<td colspan="5"></td>';
		}
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}