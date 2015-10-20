<?php

namespace Model\Entity;

/**
 * Topic Entity
 *
 * @author Petr Poupě
 */
class ForumTopicEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var int */
    protected $forumId;

    /** @var string */
    protected $name;

    /** @var \Nette\DateTime */
    protected $date;

    /** @var \Nette\DateTime */
    public $dateLastPost;

    /** @var int */
    protected $firstPostId;

    /** @var int */
    protected $lastPostId;

    /** @var ForumPostEntity */
    protected $firstPost;

    /** @var ForumPostEntity */
    protected $lastPost;

    /** @var int */
    protected $countPosts = 0;

    /** @var int */
    protected $countViews = 0;

}

?>
