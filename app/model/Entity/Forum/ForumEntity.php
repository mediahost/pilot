<?php

namespace Model\Entity;

/**
 * Forum Entity
 *
 * @author Petr Poupě
 */
class ForumEntity extends Entity
{

    /** @var int */
    protected $id;

    /** @var int */
    protected $categoryId;

    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var \Nette\DateTime */
    protected $date;

    /** @var \Nette\DateTime */
    protected $dateLastTopic;

    /** @var \Nette\DateTime */
    protected $dateLastPost;

    /** @var int */
    protected $countTopics = 0;

    /** @var int */
    protected $countPosts = 0;

    /** @var int */
    protected $lastPostId;

    /** @var ForumPostEntity */
    protected $lastPost;

}

?>
