<?php

namespace Model\Entity;

/**
 * Description of LocationEntity
 *
 * @author Radim Křek
 */
class LocationEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var int */
    protected $parent_id;

    /** @var string */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getParent_id()
    {
        return $this->parent_id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setParent_id($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function __construct($_data)
    {
        $this->commonSet($_data);
    }

}
