<?php
namespace Model\Service;


use Model\Entity\LocationEntity,
	Model\Mapper\Dibi\LocationDibiMapper;
/**
 * Description of LocationService
 *
 * @author Radim Křek
 */
class LocationService {
	/** LocationDibiMapper */
	private $mapper;
	
	public function __construct(LocationDibiMapper $mapper){
		$this->mapper = $mapper;
	}
	
	public function find($column, $value){
		return $this->mapper->findBy($column, $value);
	}
	
	public function save(LocationEntity $data){
		if ($data->id && count($this->mapper->findBy('id = %i', $data->id))) {
			//update
			return $this->mapper->update($data->to_array());
		}
		else{
			//insert
			return $this->mapper->insert($data->to_array());
		}
	}
	
	public function delete($id)
	{
		return $this->mapper->delete($id);
	}
	
	public function findById($id)
	{
		return $this->mapper->findById($id);
	}
	
	public function getAll(){
		return $this->mapper->getAll();
	}
	
	public function saveConnection($data){
		$this->mapper->insertConn($data);
	}
	
	public function findConn($data){
		return $this->mapper->findConn($data);
	}
	
	public function deleteConn($id){
		return $this->mapper->deleteConn($id);
	}
	
	public function getMainDataGrid()
	{
		return $this->mapper->getMainDataGrid();
	}
	
	public function getSubDataGrid($id)
	{
		return $this->mapper->getSubDataGrid($id);
	}

}
