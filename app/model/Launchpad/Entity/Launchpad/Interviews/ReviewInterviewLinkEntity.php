<?php

namespace App\Model\Entity\Launchpad\Interview;

/**
 * ReviewInterviewLinkEntity
 *
 * @author Petr Poupě
 * @property string $interviewId
 * @property string $title
 * @property string $candidateId
 * @property string $customCandidateId
 * @property string $email
 * @property string $link
 * @property-read bool $isLink
 * @property-read string $linkMessage
 * @property-read string $linkUrl
 */
class ReviewInterviewLinkEntity extends \App\Model\Entity\BaseEntity
{

    /** @var string */
    private $interviewId;

    /** @var string */
    private $title;

    /** @var string */
    private $candidateId;

    /** @var string */
    private $customCandidateId;

    /** @var string */
    private $email;

    /** @var string */
    private $completionDate;

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
            if (array_key_exists("candidate_id", $link)) {
                $this->setCandidateId($link["candidate_id"]);
            }
            if (array_key_exists("custom_candidate_id", $link)) {
                $this->setCustomCandidateId($link["custom_candidate_id"]);
            }
            if (array_key_exists("email", $link)) {
                $this->setEmail($link["email"]);
            }
            if (array_key_exists("completion_date", $link)) {
                $this->setCompletionDate($link["completion_date"]);
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

    public function setCandidateId($value)
    {
        $this->candidateId = $value;
        return $this;
    }

    public function getCandidateId()
    {
        return $this->candidateId;
    }

    public function setCustomCandidateId($value)
    {
        $this->customCandidateId = $value;
        return $this;
    }

    public function getCustomCandidateId()
    {
        return $this->customCandidateId;
    }

    public function setEmail($value)
    {
        $this->email = $value;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setCompletionDate($value)
    {
        $this->completionDate = $value;
        return $this;
    }

    public function getCompletionDate()
    {
        return $this->completionDate;
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

    public function getIsLink()
    {
        $link = $this->getLink();
        return isset($link["status"]) && $link["status"] === "success";
    }

    public function getLinkMessage()
    {
        $link = $this->getLink();
        if (isset($link["message"])) {
            return $link["message"];
        } else {
            return NULL;
        }
    }

    public function getLinkUrl()
    {
        $link = $this->getLink();
        if (isset($link["url"])) {
            return $link["url"];
        } else {
            return NULL;
        }
    }

}
