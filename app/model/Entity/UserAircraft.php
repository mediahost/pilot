<?php

namespace Model\Entity;

class UserAircraft
{

	/** @var int */
	public $aircraftId;

	/** @var string */
	public $aircraftName;

	/** @var string */
	public $aircraftTypeName;

	/** @var string */
	public $aircraftType;

	/** @var int */
	public $manufacturerId;

	/** @var string */
	public $manufacturerName;

	/** @var int */
	public $hours;

	/** @var int|NULL */
	public $pic;

	/** @var bool */
	public $current;

}