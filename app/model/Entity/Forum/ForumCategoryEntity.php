<?php

namespace Model\Entity;

/**
 * Forum Category Entity
 *
 * @author Petr Poupě
 */
class ForumCategoryEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var int */
    protected $parent;

    /** @var string */
    protected $name;

    /** @var string */
    protected $lang;

    /** @var int */
    protected $priority = 1;

    /** @var bool */
    protected $active = TRUE;

    /** @var ForumEntity[] */
    protected $forums;
    
    public function getForumsCount()
    {
        return count($this->forums);
    }

}

?>
