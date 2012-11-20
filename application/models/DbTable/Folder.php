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
	
	/*******************************************************
	 * Vrací pole ID - název pro multioptions.
	 */
	public function getFolders($clientId){
		Zend_Debug::dump($clientId);
		$select = $this->select()
			->from('folder')
			->join('workplace', 'folder.id_folder = workplace.folder_id')
			->where('workplace.client_id = ?', $clientId)
			->order('folder.folder');
		$select->setIntegrityCheck(false);
		Zend_Debug::dump($select->__toString());
		$results = $this->fetchAll($select);
		Zend_Debug::dump($results);
		$folders = array();
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_folder;
				$folders[$key] = $result->folder;
			}
		}
		return $folders;
	}
	
}