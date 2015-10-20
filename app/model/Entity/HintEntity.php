<?php

namespace Model\Entity;

/**
 * Hint Entity
 *
 * @author Petr Poupě
 */
class HintEntity extends MultilangEntity
{

    public $form;
    public $comment;
    public $text;

    public function getText()
    {
        return $this->text;
    }

}

?>
