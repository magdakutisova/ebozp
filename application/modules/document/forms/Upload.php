<?php
class Document_Form_Upload extends Zend_Form {
	
	public function init() {
		
		$this->setName("formupload");
		
		$this->setElementsBelongTo("upload");
		$this->setMethod(Zend_Form::METHOD_POST);
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'div')),
				'Form',
		));
		
		$decorators = array("ViewHelper", array("Label"));
		$this->addElement("file", "file", array("required" => true, "label" => "Nahrát soubor : ", "decorators" => array(array("Label"), array("File"))));
		
		$this->addElement("hidden", "parent_id", array("decorators" => $decorators));
		$this->addElement("hidden", "file_id", array("decorators" => $decorators));
		$this->addElement("submit", "submit", array("label" => "Uploadovat", "decorators" => array("ViewHelper")));
		
		$this->getElement("file")->addValidator(new Zend_Validate_File_ExcludeExtension(
				array(
						"extensions" => "exe,dll,vbs,bat,com,so"
					)
				)
			);
	}
	
	public function setDirectory(Document_Model_Row_Directory $directory) {
		$this->_elements["parent_id"]->setValue($directory->id);
		
		return $this;
	}
}