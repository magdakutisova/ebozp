<?php
class Audit_Form_CheckEdit extends Audit_Form_CheckCreate {

	public function init() {
		parent::init();

		$submit = $this->getElement("submit");
		$this->removeElement("submit");

		// nastaveni dekoratoru
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);

		$this->addElement("textarea", "progerss_note", array(
				"label" => "Poznámka k průběhu",
				"decorators" => $elementDecorator
		));

		$this->addElement("textarea", "summary", array(
				"label" => "Shrnutí*",
				"decorators" => $elementDecorator,
				"required" => true
		));

		$this->addElement("submit", "submit", array(
				"label" => "Uložit",
				"decorators" => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', "colspan" => 2)),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
				)
		));
	}
}