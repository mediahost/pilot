<?php

namespace Model\Entity;

/**
 * Timeline Event Entity
 *
 * @author Petr Poupě
 */
class TimelineEventEntity extends Entity
{

    public function __construct($start = NULL, $end = NULL, $name = NULL, $class = NULL, $id = NULL)
    {
        $this->start = $this->returnDate($start);
        $this->end = $end === NULL ? NULL : $this->returnDate($end);
        $this->name = $this->returnString($name);
        $this->class = $class;
        $this->id = $id;
    }
    
    /** @var \Nette\DateTime */
    protected $start;

    /** @var \Nette\DateTime */
    protected $end;

    /** @var string */
    protected $name;

    /** @var string */
    protected $class;

    /** @var string */
    protected $id;

}

?>
