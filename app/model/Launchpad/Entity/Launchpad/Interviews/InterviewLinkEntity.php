<?php

namespace App\Model\Entity\Launchpad\Interview;

/**
 * InterviewLinkEntity
 *
 * @author Petr Poupě
 * @property string $interviewId
 * @property string $title
 * @property string $link
 */
class InterviewLinkEntity extends \App\Model\Entity\BaseEntity
{

    /** @var string */
    private $interviewId;

    /** @var string */
    private $title;

    /** @var string */
    private $link;

    public function __construct($link = [])
    {
        $this->convert($link);
    }

    public function convert($link)
    {
        if (is_array($link)) {
            if (array_key_exists("interview_id", $link)) {
                $this->setInterviewId($link["interview_id"]);
            }
            if (array_key_exists("title", $link)) {
                $this->setTitle($link["title"]);
            }
            if (array_key_exists("link", $link)) {
                $this->setLink($link["link"]);
            }
        }
    }

    public function setInterviewId($value)
    {
        $this->interviewId = $value;
        return $this;
    }

    public function getInterviewId()
    {
        return $this->interviewId;
    }

    public function setTitle($value)
    {
        $this->title = $value;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setLink($value)
    {
        $this->link = $value;
        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

}
