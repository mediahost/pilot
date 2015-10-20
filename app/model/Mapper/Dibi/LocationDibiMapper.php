<?php

namespace Model\Mapper\Dibi;

use Model\Entity\LocationEntity;
/**
 * Description of LocationDibiMapper
 *
 * @author Radim Křek
 */
class LocationDibiMapper extends DibiMapper{
	/** @var string Tabulka lokací */
	public $table = 'location';
	
	/** @var string Propojovací tabulka */
	public $jobsToLocation = "job_to_locations";
	
	public function findBy($column, $value){
		$data = $this->conn->select('*')->from($this->table)->where($column , $value)->fetchAll();
			$r = array();
			foreach ($data as $d){
				$r[] = new LocationEntity($d);				
			}

		return $r;
	}
	
	public function findById($id)
	{
		$data = $this->conn->select('*')->from($this->table)->where('id=%i', $id)->fetch();
		return new LocationEntity($data);
	}
	
	public function getAll(){
		$data = $this->conn->select('*')->from($this->table)->fetchAll();
		$r = array();
		foreach($data as $d){
			$r[] = new LocationEntity($d);
		}
		return $r;
	}
	
	public function insert($_data){
		return $this->conn->insert($this->table, $_data)->execute(\dibi::IDENTIFIER);
	}
	
	public function update($_data){
		return $this->conn->update($this->table, $_data)->where('is=%i', $_data['id']);
	}
	
	public function delete($id){
		$this->conn->delete($this->table)->where('id=%i', $id)->execute();
	}
	
	public function updateConn($_data){
		$this->conn->update($this->jobsToLocation, $_data)->where('job_id=%i AND location_id=%i', $_data['job_id'], $_data['location_id']);
	}
	
	public function insertConn($_data){
		return $this->conn->insert($this->jobsToLocation, $_data)->execute();
	}
	
	public function deleteConn($_id){
		$this->conn->delete($this->jobsToLocation)->where('jobs_id=%i', $_id)->execute();
	}
	
	public function findConn($_data){
		if (count($_data) > 1) {
			return $this->conn->select('*')->from($this->jobsToLocation)->where('jobs_id=%i AND location_id=%i', $_data['jobs_id'], $_data['location_id'])->fetchAll();	
		}
		else{
			if (isset($_data['jobs_id'])) {
				return $this->conn->select('*')->from($this->jobsToLocation)->where('jobs_id=%i', $_data['jobs_id'])->fetchAll();	
			}
			else{
				return $this->conn->select('*')->from($this->jobsToLocation)->where('location_id=%i', $_data['location_id'])->fetchAll();
			}
		}
	}
	
	public function getJobLocations($_id){
		$data = $this->conn->select($this->table.'.name')
				->from($this->jobsToLocation)->where($this->jobsToLocation.'.jobs_id=%i', $_id)
				->leftJoin($this->table)->on($this->jobsToLocation.'.location_id='.$this->table.'.id')
				->fetchAll();
		$r=array();
		foreach($data as $d){
			$r[]=$d['name'];
		}
		return $r;
	}
	
	public function getMainDataGrid()
    {
        return $this->conn->select('*')
                        ->from($this->table)
                        ->where('parent_id IS', NULL);
    }
	
	public function getSubDataGrid($id)
	{
		return $this->conn->select('*')
						->from($this->table)
						->where('parent_id=%i', $id);
	}
}
