<?php

class Planning_Form_Item extends Zend_Form {

    const DATE_PATTERN = "^([0-9]{4}(-[0-9]{2}){2})( [0-9]{2}(:[0-9]{2}){1,2})?$";

    public function init() {
        parent::init();
        
        $this->removeElement("submit");
        
        // nastaveni dat
        $this->setName("planning-item");
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setElementsBelongTo("planning");
        
        // nastaveni dekoratoru
        $this->setDecorators(array(
                'FormElements',
                array('HtmlTag', array('tag' => 'table')),
                'Form',
        ));
        
        $elementDecorator = array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        );
        
        $submitDecorator = array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        );

        $this->addElement("text", "name", array(
            "label" => "Název úkolu",
            "required" => true,
            "decorators" => $elementDecorator
            ));

        $this->addElement("select", "task_type", array(
            "label" => "Typ úkolu",
            "multiOptions" => array("" => "-- VYBERTE --"),
            "required" => true,
            "decorators" => $elementDecorator,
            "filters" => array(new Zend_Filter_Null())
            ));

        $this->addElement("select". "user_id", array(
            "label" => "Přiřazeno uživateli:",
            "filters" => array(new Zend_Filter_Null())
            ));

        $this->addElement("textarea", "description", array(
            "label" => "Popis/poznámka",
            "required" => false,
            "decorators" => $elementDecorator));

        $this->addElement("text", "planned_on", array(
            "label" => "Naplánováno na",
            "decorators" => $elementDecorator,
            "pattern" => self::DATE_PATTERN
            ));

        $this->addElement("text", "planned_from", array(
            "label" => "Provést od",
            "decorators" => $elementDecorator,
            "pattern" => self::DATE_PATTERN
            ));

        $this->addElement("text", "planned_to", array(
            "label" => "Provést do",
            "decorators" => $elementDecorator,
            "pattern" => self::DATE_PATTERN
            ));

        $this->addElement("submit", "submit", array(
            "label" => "Uložit",
            "decorators" => $submitDecorator));
    }
}