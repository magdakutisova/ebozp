<?php

class Document_Model_DocumentationsCategories extends Zend_Db_Table_Abstract {

    protected $_name = "document_documentations_categories";

    protected $_sequence = true;

    protected $_primary = array("id");

    protected $_referenceMap = array(
        "client" => array(
            "columns" => "client_id",
            "refTableClass" => "Application_Model_Client",
            "refColumns" => "id_client"
        ));

    protected $_documentTable = "Document_Model_Documentations";

    public function findByClient($clientId, $subsidiaryId = null, $withCentral=true) {
        // sestaveni zakladniho dotazu
        $select = new Zend_Db_Select($this->getAdapter());
        $select->from(array("c" => $this->_name));

        $select->where("c.client_id = ?", $clientId)->order("name");

        // propojeni na referencni tabulku dokumentu
        $tableDocuments = new $this->_documentTable();
        $nameDocuments = $tableDocuments->info("name");

        $select->joinInner(array("d" => $nameDocuments), "d.category_id = c.id", array("cnt" => new Zend_Db_Expr("COUNT(d.id)")))->having("cnt > 0")->group("c.id");

        if ($subsidiaryId > 0) {
            $where = "d.subsidiary_id = ?";

            if ($withCentral) $where .= " OR d.subsidiary_id IS NULL";

            $select->where($where, $subsidiaryId);
        } elseif ($subsidiaryId == 0) {
            $select->where("d.subsidiary_id IS NULL");
        }

        $data = $select->query()->fetchAll();

        return new Zend_Db_Table_Rowset(array("data" => $data, "stored" => true, "table" => $this));
    }
}