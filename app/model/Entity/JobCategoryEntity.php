<?php

namespace Model\Entity;

/**
 * Description of JobCategoryEntity
 *
 * @author Radim Křek
 */
class JobCategoryEntity extends Entity
{

	/** @var int */
	protected $id;

	/** @var string */
	protected $lang;

	/** @var string */
	protected $name;

	public function __construct($data = NULL)
	{
		if ($data !== NULL)
		{
			$this->commonSet($data);
		}
	}

	public function to_array(array $notIncluded = array())
	{
		return parent::to_array($notIncluded);
	}

}
