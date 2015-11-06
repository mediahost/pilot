<?php

namespace Model\Service;

use Model\Mapper\Dibi\AircraftDibiMapper;

class AircraftService
{

	const TYPE_JET = 1;
	const TYPE_TURBO = 2;

	/** @var AircraftDibiMapper */
	private $aircraftDibiMapper;

	public function __construct(AircraftDibiMapper $aircraftDibiMapper)
	{
		$this->aircraftDibiMapper = $aircraftDibiMapper;
	}

	public function getManufacturers($type = NULL)
	{
		return $this->aircraftDibiMapper->getManufacturers($type);
	}

	public function getModels($type = NULL, $manufacturer = NULL)
	{
		return $this->aircraftDibiMapper->getModels($type, $manufacturer);
	}

	public static function getTypeName($id)
	{
		switch ($id) {
			case self::TYPE_JET: return 'jet';
			case self::TYPE_TURBO: return 'turbo';
		}
	}

}
