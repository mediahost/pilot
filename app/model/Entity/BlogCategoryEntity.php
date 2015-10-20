<?php

namespace Model\Entity;

/**
 * Blog Category Entity
 *
 * @author Petr Poupě
 */
class BlogCategoryEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $lang;

    /** @var string */
    protected $name;

    /** @var bool */
    protected $active = 1;

}

?>
