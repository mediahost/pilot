<?php

namespace Model\Entity;

/**
 * Job Apply Entity
 *
 * @author Petr Poupě
 */
class JobApplyEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var int */
    protected $userId;

    /** @var int */
    protected $jobId;

    /** @var string */
    protected $jobExtId;

    /** @var \Nette\DateTime */
    protected $datetime;

    /** @var string */
    protected $position;

    /** @var string */
    protected $reciever;

    /** @var string */
    protected $sender;

    /** @var string */
    protected $subject;

    /** @var string */
    protected $text;

}

?>
