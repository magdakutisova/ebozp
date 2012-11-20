<?php
class Application_Model_Folder{
	
	private $idFolder;
	private $folder;
	private $clientId;
	
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
	
	public function getClientId(){
		return $this->clientId;
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
	
	public function setClientId($clientId){
		$this->clientId = $clientId;
	}

	public function populate(array $data){
		$this->idFolder = isset($data['id_folder']) ? $data['id_folder'] : null;
		$this->folder = isset($data['folder']) ? $data['folder'] : null;
		$this->clientId = isset($data['client_id']) ? $data['client_id'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_folder'] = $this->idFolder;
		}
		$data['folder'] = $this->folder;
		$data['client_id'] = $this->clientId;
		
		return $data;
	}
	
}