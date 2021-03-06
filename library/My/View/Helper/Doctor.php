<?php
class My_View_Helper_Doctor extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function doctor($name, $value = null, $attribs = null){
		$this->html = '';
		$idDoctor = $names = $street = $town = '';
		
		$calledFrom = isset($attribs['calledFrom']) ? $attribs['calledFrom'] : null;
		
		if($value){
			$idDoctor = $value['id_doctor'];
			$names = $value['name'];
			$street = $value['street'];
			$town = $value['town'];
		}
		
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		$helperButton = new Zend_View_Helper_FormButton();
		$helperButton->setView($this->view);
		
		if($calledFrom == 'subs'){
			$this->html .= '<tr id="' . $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_doctor]', $idDoctor);
			$this->html .= '<td><label for="' . $name . '[name]">Jméno a příjmení</label><br/>' . $helperText->formText($name . '[name]' , $names) . '</td>';
			$this->html .= '<td><label for="' . $name . '[street]">Ulice</label><br/>' . $helperText->formText($name . '[street]', $street) . '</td>';
			$this->html .= '<td><label for="' . $name . '[town]">Město</label><br/>' . $helperText->formText($name . '[town]', $town) . '</td>';
			$this->html .= '<td>' . $helperButton->formButton($name . '[delete]', 'Odebrat', array('class' => 'deleteDoctor_subs')) . '</td>';
			$this->html .= '</tr>';
		}
		else{
			$this->html .= '<tr id="' . $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_doctor]', $idDoctor);
			$this->html .= '<td><label for="' . $name . '[name]">Jméno a příjmení</label><br/>' . $helperText->formText($name . '[name]' , $names) . '</td>';
			$this->html .= '<td><label for="' . $name . '[street]">Ulice</label><br/>' . $helperText->formText($name . '[street]', $street) . '</td>';
			$this->html .= '<td><label for="' . $name . '[town]">Město</label><br/>' . $helperText->formText($name . '[town]', $town) . '</td>';
			$this->html .= '<td>' . $helperButton->formButton($name . '[delete]', 'Odebrat', array('class' => 'deleteDoctor')) . '</td>';
			$this->html .= '</tr>';
		}

		return $this->html;
	}
	
}