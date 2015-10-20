<?php

namespace Pupek\TraversalMenu\Model;

/**
 * Node Entity
 *
 * @author Petr Poupě
 */
class NodeEntity extends \Nette\Object
{

    public $id;
    public $name;
    public $parentId;
    public $left;
    public $right;
    public $deep;
    public $link;

    protected function setLink($value)
    {
        return $this->link = $value;
    }

}

?>
