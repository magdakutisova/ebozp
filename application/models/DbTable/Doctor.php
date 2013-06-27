<?php
class Application_Model_DbTable_Doctor extends Zend_Db_Table_Abstract{
	
	protected $_name = 'doctor';
	
	protected $_referenceMap = array('Subsidiary' => array(
			'columns' => 'subsidiary_id',
			'refTableClass' => 'Application_Model_DbTable_Subsidiary',
			'refColumns' => 'id_subsidiary'
			));
	
	public function getDoctor($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_doctor = ' . $id);
		if(!$row){
			throw new Exception("Poskytovatel pracovnělékařské péče $id nebyl nalezen.");
		}
		$doctor = $row->toArray();
		return new Application_Model_Doctor($doctor);
	}
	
	public function addDoctor(Application_Model_Doctor $doctor){
		$data = $doctor->toArray();
		$doctorId = $this->insert($data);
		return $doctorId;
	}
	
	public function updateDoctor(Application_Model_Doctor $doctor){
		$data = $doctor->toArray();
		$this->update($data, 'id_doctor = ' . $doctor->getIdDoctor());
	}
	
	public function deleteDoctor($id){
		$this->delete('id_doctor = ' . (int)$id);
	}
	
}