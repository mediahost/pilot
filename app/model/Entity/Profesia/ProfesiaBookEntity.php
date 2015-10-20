<?php

namespace Model\Entity;

/**
 * Profesia Book Entity
 *
 * @author Petr Poupě
 */
class ProfesiaBookEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var type */
    protected $type;

    /** @var string */
    protected $lang;

    /** @var string */
    protected $name;

    /** @var string */
    protected $web;

    /** @var int */
    protected $attrId;

    /** @var int */
    protected $attrCategory;

    /** @var int */
    protected $attrPosition;

    /** @var int */
    protected $attrParentId;

    /** @var int */
    protected $attrCatLevelId;

}

?>
