<?php

namespace Model\Entity;

/**
 * Post Entity
 *
 * @author Petr Poupě
 */
class ForumPostEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var int */
    protected $topicId;

    /** @var int */
    protected $userId;

    /** @var string */
    protected $username;

    /** @var string */
    protected $body;

    /** @var \Nette\DateTime */
    protected $date;

}

?>
