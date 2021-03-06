<?php
class My_View_Helper_ContactPerson extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function contactPerson($name, $value = null, $attribs = null){
		$this->html = '';
		$idContactPerson = $names = $phone = $email = '';
		
		$calledFrom = isset($attribs['calledFrom']) ? $attribs['calledFrom'] : null;
		
		if($value){
			$idContactPerson = $value['id_contact_person'];
			$names = $value['name'];
			$phone = $value['phone'];
			$email = $value['email'];
		}
		
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		$helperButton = new Zend_View_Helper_FormButton();
		$helperButton->setView($this->view);
		
		if($calledFrom == 'subs'){
			$this->html .= '<tr id="' . $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_contact_person]', $idContactPerson);
			$this->html .= '<td><label for="' . $name . '[name]">Jméno a příjmení</label><br/>' . $helperText->formText($name . '[name]' , $names) . '</td>';
			$this->html .= '<td><label for="' . $name . '[phone]">Telefon</label><br/>' . $helperText->formText($name . '[phone]', $phone) . '</td>';
			$this->html .= '<td><label for="' . $name . '[email]">Email</label><br/>' . $helperText->formText($name . '[email]', $email) . '</td>';
			$this->html .= '<td>' . $helperButton->formButton($name . '[delete]', 'Odebrat', array('class' => 'deleteContactPerson_subs')) . '</td>';
			$this->html .= '</tr>';
		}
		else{
			$this->html .= '<tr id="' . $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_contact_person]', $idContactPerson);
			$this->html .= '<td><label for="' . $name . '[name]">Jméno a příjmení</label><br/>' . $helperText->formText($name . '[name]' , $names) . '</td>';
			$this->html .= '<td><label for="' . $name . '[phone]">Telefon</label><br/>' . $helperText->formText($name . '[phone]', $phone) . '</td>';
			$this->html .= '<td><label for="' . $name . '[email]">Email</label><br/>' . $helperText->formText($name . '[email]', $email) . '</td>';
			$this->html .= '<td>' . $helperButton->formButton($name . '[delete]', 'Odebrat', array('class' => 'deleteContactPerson')) . '</td>';
			$this->html .= '</tr>';
		}

		return $this->html;
	}
	
}