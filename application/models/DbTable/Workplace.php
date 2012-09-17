<?php
class Application_Model_DbTable_Workplace extends Zend_Db_Table_Abstract {
	
	protected $_name = 'workplace';
	
	public function getWorkplace($id){
		$id = (int) $id;
		$row = $this->fetchRow('id_workplace = ' . $id);
		if (!$row){
			throw new Exception("Pracoviště $id nebylo nalezeno.");
		}
		$workplace = $row->toArray();
		return new Application_Model_Workplace($workplace);
	}
	
	public function addWorkplace(Application_Model_Workplace $workplace){
		$data = $workplace->toArray();
		$workplaceId = $this->insert($data);
		return $workplaceId;
	}
	
	public function updateWorkplace(Application_Model_Workplace $workplace){
		$data = $workplace->toArray();
		$this->update($data, 'id_workplace = ' . $workplace->getIdWorkplace());
	}
	
	public function deleteWorkplace($id){
		$this->delete('id_workplace = ' . (int)$id);
	}
	
	public function getBySubsidiary($subsidiaryId){
		$select = $this->select()
			->from('workplace')
			->where('subsidiary_id = ?', $subsidiaryId);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	/****************************************
	 * Řazeno podle pobočky.
	 */
	public function getByClient($clientId){
		$select = $this->select()
			->from('workplace')
			->join('subsidiary', 'subsidiary.id_subsidiary = workplace.subsidiary_id')
			->where('subsidiary.client_id = ?', $clientId)
			->order('subsidiary.subsidiary_name');
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		if ($result->count()){
			$workplaces = array();
			foreach($result as $workplace){
				$workplace = $result->current();
				$workplaces[$workplace->subsidiary_name][] = $this->processWorkplace($workplace);
			}
			return $workplaces;
		}
		else{
			return null;
		}
	}
	
	private function process($result){
		if ($result->count()){
			$workplaces = array();
			foreach($result as $workplace){
				$workplace = $result->current();
				$workplaces[] = $this->processWorkplace($workplace);
			}
			return $workplaces;
		}
	}
	
	private function processWorkplace($workplace){
		$data = $workplace->toArray();
		return new Application_Model_Workplace($data);
	}
	
}