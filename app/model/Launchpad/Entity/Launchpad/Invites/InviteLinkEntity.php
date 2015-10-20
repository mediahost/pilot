<?php

namespace App\Model\Entity\Launchpad\Invites;

/**
 * InviteLinkEntity
 *
 * @author Petr Poupě
 * @property int $interviewId
 * @property string $customInterviewId
 * @property string $customInviteId
 * @property string $candidateId
 * @property string $customCandidateId
 * @property string $title
 * @property string $email
 * @property string $link
 * @property-read bool $isLink
 * @property-read string $linkMessage
 * @property-read string $linkUrl
 */
class InviteLinkEntity extends \App\Model\Entity\BaseEntity
{

    /** @var string */
    private $interviewId;

    /** @var string */
    private $customInterviewId;

    /** @var string */
    private $customInviteId;

    /** @var string */
    private $candidateId;

    /** @var string */
    private $customCandidateId;

    /** @var string */
    private $title;

    /** @var string */
    private $email;

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
            if (array_key_exists("custom_interview_id", $link)) {
                $this->setCustomInterviewId($link["custom_interview_id"]);
            }
            if (array_key_exists("custom_invite_id", $link)) {
                $this->setCustomInviteId($link["custom_invite_id"]);
            }
            if (array_key_exists("candidate_id", $link)) {
                $this->setCandidateId($link["candidate_id"]);
            }
            if (array_key_exists("custom_candidate_id", $link)) {
                $this->setCustomCandidateId($link["custom_candidate_id"]);
            }
            if (array_key_exists("title", $link)) {
                $this->setTitle($link["title"]);
            }
            if (array_key_exists("email", $link)) {
                $this->setEmail($link["email"]);
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

    public function setCustomInterviewId($value)
    {
        $this->customInterviewId = $value;
        return $this;
    }

    public function getCustomInterviewId()
    {
        return $this->customInterviewId;
    }

    public function setCustomInviteId($value)
    {
        $this->customInviteId = $value;
        return $this;
    }

    public function getCustomInviteId()
    {
        return $this->customInviteId;
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

    public function setTitle($value)
    {
        $this->title = $value;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
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
