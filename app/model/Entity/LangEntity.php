<?php

namespace Model\Entity;

/**
 * Lang Entity
 *
 * @author Petr Poupě
 */
class LangEntity extends Entity
{

    public $id;
    public $key;
    public $code;
    public $name;
    public $class;
    public $order;
    public $published = FALSE;

}

?>
