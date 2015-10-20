<?php

namespace App\Model\Entity;

/**
 * BaseEntity
 *
 * @author Petr Poupě
 */
class BaseEntity extends \Nette\Object
{

    /** @var int */
    private $id;
    
    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }

}
