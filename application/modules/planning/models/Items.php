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
}