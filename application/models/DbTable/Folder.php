<?php
class Application_Model_DbTable_Folder extends Zend_Db_Table_Abstract{
	
	protected $_name = 'folder';
	
	public function getFolder($id){
		$id = (int) $id;
		$row = $this->fetchRow('id_folder = ?', $id);
		if(!$row){
			throw new Exception("Adresář $id nebyl nalezen.");
		}
		$folder = $row->toArray();
		return new Application_Model_Folder($folder);
	}
	
	public function addFolder(Application_Model_Folder $folder){
		$data = $folder->toArray();
		$folderId = $this->insert($data);
		return $folderId;
	}
	
	public function deleteFolder($id){
		$this->delete('id_folder = ?' . (int)$id);
	}
	
}