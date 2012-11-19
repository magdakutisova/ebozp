<?php
class Application_Model_Folder{
	
	private $idFolder;
	private $folder;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	
	/**
	 * @return the $idFolder
	 */
	public function getIdFolder() {
		return $this->idFolder;
	}

	/**
	 * @return the $folder
	 */
	public function getFolder() {
		return $this->folder;
	}

	/**
	 * @param $idFolder the $idFolder to set
	 */
	public function setIdFolder($idFolder) {
		$this->idFolder = $idFolder;
	}

	/**
	 * @param $folder the $folder to set
	 */
	public function setFolder($folder) {
		$this->folder = $folder;
	}

	public function populate(array $data){
		$this->idFolder = isset($data['id_folder']) ? $data['id_folder'] : null;
		$this->folder = isset($data['folder']) ? $data['folder'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_folder'] = $this->idFolder;
		}
		$data['folder'] = $this->folder;
		
		return $data;
	}
	
}