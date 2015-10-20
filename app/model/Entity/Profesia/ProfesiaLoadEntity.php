<?php

namespace Model\Entity;

/**
 * Profesia Load Entity
 *
 * @author Petr Poupě
 */
class ProfesiaLoadEntity extends Entity
{

    protected $id;

    /** @var \Nette\DateTime */
    protected $lastModified;

    /** @var \Nette\DateTime */
    protected $loadTime;

    public function setLastModified($value)
    {
        $this->lastModified = $this->returnDate($value);
    }

    public function setLoadTime($value)
    {
        $this->loadTime = $this->returnDate($value);
    }

}

?>
