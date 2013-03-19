<?php
class Audit_Form_WorkplaceComment extends Zend_Form {
	
	public function init() {
		
		$this->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'div')),
				'Form',
		));
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'div')),
				array('Label', array('tag' => 'legend')),
				array(array('row' => 'HtmlTag'), array('tag' => 'fieldset', "openOnly" => true)),
		);
		
		$submitDecoratorOpen = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'div', "openOnly" => true)),
				array(array('row' => 'HtmlTag'), array('tag' => 'div', "openOnly" => true)),
		);
		
		$submitDecoratorClose = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'div', "closeOnly" => true)),
				array(array('row' => 'HtmlTag'), array('tag' => 'fieldset', "closeOnly" => true)),
		);
		
		$this->addElement("textarea", "comment", array(
				"label" => "Komentář",
				"decorators" => $elementDecorator
		));
		
		$this->addElement("hidden", "workplace_id", array("decorators" => $submitDecoratorOpen));
		$this->addElement("hidden", "create", array("decorators" => array("ViewHelper")));
		
		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => $submitDecoratorClose
		));
	}
	
	public function populate(array $data) {
		parent::populate($data);
		
		if (isset($data["name"])) $this->setCommentLabel($data["name"]);
		return $this;
	}
	
	/**
	 * nastavi jmeno pracivste jako label textarey
	 * 
	 * @param string $workplaceName jmeno pracoviste
	 * @return Audit_Form_WorkplaceComment
	 */
	public function setCommentLabel($workplaceName) {
		$this->getElement("comment")->setLabel($workplaceName);
		
		return $this;
	}
}