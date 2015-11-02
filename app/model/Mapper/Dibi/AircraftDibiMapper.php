<?php

namespace Model\Mapper\Dibi;

class AircraftDibiMapper extends DibiMapper
{

	public function getManufacturers($type = NULL)
	{
		$selection = $this->conn
			->select('aircraft_manufacturer.*')
			->from('aircraft_manufacturer');
		if ($type !== NULL) {
		    $selection->join('aircraft')
				->on('aircraft.aircraft_manufacturer_id = aircraft_manufacturer.id')
				->groupBy('aircraft_manufacturer.id')
				->where('aircraft.type = %i', $type);
		}

		return $selection->fetchPairs('id', 'name');
	}

	public function getModels($type = NULL, $manufacturer = NULL)
	{
		$selection = $this->conn
			->select('*')
			->from('aircraft');
		if ($type !== NULL) {
			$selection->where('type = %i', $type);
		}
		if ($manufacturer !== NULL) {
			$selection->where('aircraft_manufacturer_id = %i', $manufacturer);
		}
		return $selection->fetchPairs('id', 'name');
	}

}
