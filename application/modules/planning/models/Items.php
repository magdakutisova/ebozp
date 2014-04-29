<?php

class Planning_Model_Items extends Zend_Db_Table_Abstract {

    /**
     * konstanty vyjadrujici typy ukolu
     */
    const TASK_AUDIT = 0;
    const TASK_CHECK = 1;
    const TASK_WATCH = 2;

    protected $_name = "planning_items";

    protected $_sequence = true;

    protected $_primary = array("id");

    protected $_referenceMap = array(
        "user" => array(
            "columns" => "user_id",
            "rafTableClass" => "Application_Model_DbTable_User",
            "refColumns" => "id_user"
            ),
        "creator" => array(
            "columns" => "created_by",
            "refTableClass" => "Application_Model_DbTable_User",
            "refColumns" => "id_user"));

    public static function getTaskTypes() {
        return array(
            self::TASK_WATCH => "Dohlídka",
            self::TASK_CHECK => "Prověrka",
            self::TASK_AUDIT => "Audit"
            );
    }

    public function findById($itemId) {
        // zakladni vyhledavaci dotaz
        $select = $this->prepareSelect();

        $select->where("t.id = ?", $itemId);

        return new $this->_rowClass(array("data" => $select->query()->fetch(), "stored" => true));
    }

    public function findBySubsidiary($subsidiaryId) {
        $select = $this->prepareSelect();
        $select->where("t.subsidiary_id = ?", $subsidiaryId)->order("planned_on");

        return new $this->_rowsetClass(array("data" => $select->query()->fetchAll(), "stored" => true));
    }

    public function getItems() {
        $select = $this->prepareSelect();
        $select->columns(array("planned_on" => new Zend_Db_Expr("IFNULL(planned_on, planned_from)")));

        $select->order(array("planned_on", new Zend_Db_Expr("t.user_id is not null"), "u.name"));
        $select->where("planned_on is not null");

        return new Zend_Db_Table_Rowset(array("data" => $select->query()->fetchAll(), "stored" => true));
    }

    public function getUsers() {
        $select = $this->prepareSelect();
        $select->group("t.user_id");
        $select->order(array(new Zend_Db_Expr("t.user_id is not null"), "u.name"));
        $select->columns(array("cnt" => new Zend_Db_Expr("Count(*)")));
        $select->where("planned_on is not null");

        return new Zend_Db_Table_Rowset(array("data" => $select->query()->fetchAll(), "stored" => true));
    }

    /**
     * pripravi vyhledavaci dotaz
     * @return Zend_Db_Select
     */
    public function prepareSelect() {
        $tableUsers = new Application_Model_DbTable_User();
        
        $select = new Zend_Db_Select($this->getAdapter());
        $select->from(array("t" => $this->_name));
        $select->joinLeft(array("u" => $tableUsers->info("name")), "u.id_user = t.user_id", array("realname" => "name", "u.id_user"));

        // provazani na tvurce
        $select->joinInner(array("uc" => $tableUsers->info("name")), "uc.id_user = t.created_by", array("creatorname" => "uc.name"));

        // provazani na klienta a pobocku
        $tableClients = new Application_Model_DbTable_Client();
        $select->joinInner(array("c" => $tableClients->info("name")), "c.id_client = t.client_id", array("company_name"));

        $tableSubsidiary = new Application_Model_DbTable_Subsidiary();
        $select->joinLeft(array("s" => $tableSubsidiary->info("name")), "s.id_subsidiary = t.subsidiary_id", array("subsidiary_name", "subsidiary_town", "subsidiary_street"));

        return $select;
    }
}