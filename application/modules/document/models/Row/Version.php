<?php
class Document_Model_Row_Version extends Zend_Db_Table_Row_Abstract {
	
	public function path() {
		return DOCUMENT_PATH_DIR . "/revision_" . $this->_data["id"] . ".dat";
	}
}