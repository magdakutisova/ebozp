<?php
class Application_Model_DbTable_SubsidiaryHasPosition extends Zend_Db_Table_Abstract{
	
	protected $_name = 'subsidiary_has_position';
	
	protected $_referenceMap = array(
		'Subsidiary' => array(
			'columns' => array('id_subsidiary'),
			'refTableClass' => 'Application_Model_DbTable_Subsidiary',
			'refColumns' => array('id_subsidiary'),
		),
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
	);
	
	public function getSubsidiaries($positionId){
		$select = $this->select()
			->where('id_position = ?', $positionId);
		$results = $this->fetchAll($select);
		$subsidiaries = array();
		foreach($results as $result){
			$subsidiaries[] = $result->id_subsidiary;
		}
		return $subsidiaries;
	}
	
	public function removeRelation($subsidiaryId, $positionId){
		$this->delete(array(
				'id_subsidiary = ?' => $subsidiaryId,
				'id_position = ?' => $positionId,
		));
	}
	
	public function addRelation($subsidiaryId, $positionId){
		try{
			$data['id_subsidiary'] = $subsidiaryId;
			$data['id_position'] = $positionId;
			$this->insert($data);
		}
		catch(Exception $e){
			//porušení integrity se ignoruje
		}
	}
	
}