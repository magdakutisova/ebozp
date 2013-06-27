<?php
class My_View_Helper_Doctor extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function doctor($name, $value = null, $attribs = null){
		$this->html = '';
		$idDoctor = $names = $phone = $email = '';
		
		if($value){
			$idDoctor = $value['id_doctor'];
			$names = $value['name'];
			$phone = $value['phone'];
			$email = $value['email'];
		}
		
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_doctor]', $idDoctor);
		$this->html .= '<td><label for="' . $name . '[name]">Jméno a příjmení</label><br/>' . $helperText->formText($name . '[name]' , $names) . '</td>';
		$this->html .= '<td><label for="' . $name . '[phone]">Telefon</label><br/>' . $helperText->formText($name . '[phone]', $phone) . '</td>';
		$this->html .= '<td><label for="' . $name . '[email]">Email</label><br/>' . $helperText->formText($name . '[email]', $email) . '</td>';
		$this->html .= '</tr>';

		return $this->html;
	}
	
}