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
            "multiOptions" => Planning_Model_Items::getTaskTypes(),
            "required" => true,
            "decorators" => $elementDecorator,
            "filters" => array(new Zend_Filter_Null())
            ));

        $this->addElement("select", "user_id", array(
            "label" => "Přiřazeno uživateli:",
            "filters" => array(new Zend_Filter_Null()),
            "decorators" => $elementDecorator,
            "multiOptions" => array("" => "-- VYBERTE --")
            ));

        $this->addElement("textarea", "description", array(
            "label" => "Popis/poznámka",
            "required" => false,
            "decorators" => $elementDecorator));

        $this->addElement("text", "planned_on", array(
            "label" => "Naplánováno na",
            "decorators" => $elementDecorator,
            "pattern" => self::DATE_PATTERN,
            "filters" => array(new Zend_Filter_Null())
            ));

        $this->addElement("text", "planned_from", array(
            "label" => "Provést od",
            "decorators" => $elementDecorator,
            "pattern" => self::DATE_PATTERN,
            "filters" => array(new Zend_Filter_Null())
            ));

        $this->addElement("text", "planned_to", array(
            "label" => "Provést do",
            "decorators" => $elementDecorator,
            "pattern" => self::DATE_PATTERN,
            "filters" => array(new Zend_Filter_Null())
            ));

        $this->addElement("submit", "submit", array(
            "label" => "Uložit",
            "decorators" => $submitDecorator));
    }

    /**
     * nastavi seznam uzivatelu
     * seznam muze byt zadan jako asociativni pole nebo jako rowset z tabulky Applicaion_Model_DbTable_User
     *
     * @param array|Zend_Db_Table_Rowset_Abstract $users seznam uzivatelu
     * @param bool $addEmpty pokud je True, vlozi moznost vyberu prazdne hodnoty
     */
    public function setTargetUsers($users, $addEmpty = true) {
        // vyhodnoceni typu parametru
        $data = array("" => "-- VYBERTE --");

        if ($users instanceof Zend_Db_Table_Rowset_Abstract) {
            foreach ($users as $user) {
                $data[$user->id_user] = $user->name;
            }
        } else {
            $data = $users;
        }

        // vyhodnoceni, zda se ma pridat defaultni uzivatel
        if (!$addEmpty) {
            array_shift($data);
        }

        $this->_elements["user_id"]->setMultiOptions($data);
    }

    /**
     * vlozi seznam cilovych uzivatelu do vyberu
     * @param array $roles seznam roli uzivatelu, ktere budou k dispozici ve vyberu
     * @param bool $addEmpty prepinac, zda se ma vlozit moznost prazdneho uzivatele
     */
    public function setUsersFromTable(array $roles = array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR, My_Role::ROLE_TECHNICIAN), $addEmpty = true) {
        // nacteni dat z databaze
        $tableUsers = new Application_Model_DbTable_User();
        $users = $tableUsers->fetchAll(array("role in (?)" => $roles), "name");

        $this->setTargetUsers($users, $addEmpty);
    }
}