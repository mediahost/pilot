<?php

namespace App\Model\Entity\Launchpad\Interview;

/**
 * InterviewEntity
 *
 * @author Petr Poupě
 */
class InterviewEntity extends \App\Model\Entity\BaseEntity
{

    /** @var string */
    private $interviewId;

    /** @var string */
    private $customInterviewId;

    /** @var string */
    private $accountId;

    /** @var string */
    private $title;

    /** @var string */
    private $responsibilities;

    /** @var string */
    private $qualifications;

    /** @var string */
    private $rerecord;

    /** @var string */
    private $deadline;

    /** @var string */
    private $introductionMessage;

    /** @var string */
    private $closingMessage;

    /** @var string */
    private $timeLimit;

    /** @var string */
    private $redirectUrl;

    /** @var string */
    private $redirectButtonName;

    /** @var string */
    private $showRedirectButton;

    /** @var string */
    private $defaultLanguage;

    /** @var string */
    private $questions;

    /** @var string */
    private $filterQuestions;

    /** @var string */
    private $strictFilter;

    public function __construct($interview = [])
    {
        $this->convert($interview);
    }

    public function convert($interview)
    {
        if (is_array($interview)) {
            if (array_key_exists("interview_id", $interview)) {
                $this->setInterviewId($interview["interview_id"]);
            }
            if (array_key_exists("custom_interview_id", $interview)) {
                $this->setCustomInterviewId($interview["custom_interview_id"]);
            }
            if (array_key_exists("account_id", $interview)) {
                $this->setAccountId($interview["account_id"]);
            }
            if (array_key_exists("title", $interview)) {
                $this->setTitle($interview["title"]);
            }
            if (array_key_exists("responsibilities", $interview)) {
                $this->setResponsibilities($interview["responsibilities"]);
            }
            if (array_key_exists("qualifications", $interview)) {
                $this->setQualifications($interview["qualifications"]);
            }
            if (array_key_exists("rerecord", $interview)) {
                $this->setRerecord($interview["rerecord"]);
            }
            if (array_key_exists("deadline", $interview)) {
                $this->setDeadline($interview["deadline"]);
            }
            if (array_key_exists("introduction_message", $interview)) {
                $this->setIntroductionMessage($interview["introduction_message"]);
            }
            if (array_key_exists("closing_message", $interview)) {
                $this->setClosingMessage($interview["closing_message"]);
            }
            if (array_key_exists("time_limit", $interview)) {
                $this->setTimeLimit($interview["time_limit"]);
            }
            if (array_key_exists("redirect_url", $interview)) {
                $this->setRedirectUrl($interview["redirect_url"]);
            }
            if (array_key_exists("redirect_button_name", $interview)) {
                $this->setRedirectButtonName($interview["redirect_button_name"]);
            }
            if (array_key_exists("show_redirect_button", $interview)) {
                $this->setShowRedirectButton($interview["show_redirect_button"]);
            }
            if (array_key_exists("default_language", $interview)) {
                $this->setDefaultLanguage($interview["default_language"]);
            }
            if (array_key_exists("questions", $interview)) {
                $this->setQuestions($interview["questions"]);
            }
            if (array_key_exists("filter_questions", $interview)) {
                $this->setFilterQuestions($interview["filter_questions"]);
            }
            if (array_key_exists("strict_filter", $interview)) {
                $this->setStrictFilter($interview["strict_filter"]);
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

    public function setAccountId($value)
    {
        $this->accountId = $value;
        return $this;
    }

    public function getAccountId()
    {
        return $this->accountId;
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

    public function setResponsibilities($value)
    {
        $this->responsibilities = $value;
        return $this;
    }

    public function getResponsibilities()
    {
        return $this->responsibilities;
    }

    public function setQualifications($value)
    {
        $this->qualifications = $value;
        return $this;
    }

    public function getQualifications()
    {
        return $this->qualifications;
    }

    public function setRerecord($value)
    {
        $this->rerecord = $value;
        return $this;
    }

    public function getRerecord()
    {
        return $this->rerecord;
    }

    public function setDeadline($value)
    {
        $this->deadline = $value;
        return $this;
    }

    public function getDeadline()
    {
        return $this->deadline;
    }

    public function setIntroductionMessage($value)
    {
        $this->introductionMessage = $value;
        return $this;
    }

    public function getIntroductionMessage()
    {
        return $this->introductionMessage;
    }

    public function setClosingMessage($value)
    {
        $this->closingMessage = $value;
        return $this;
    }

    public function getClosingMessage()
    {
        return $this->closingMessage;
    }

    public function setTimeLimit($value)
    {
        $this->timeLimit = $value;
        return $this;
    }

    public function getTimeLimit()
    {
        return $this->timeLimit;
    }

    public function setRedirectUrl($value)
    {
        $this->redirectUrl = $value;
        return $this;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function setRedirectButtonName($value)
    {
        $this->redirectButtonName = $value;
        return $this;
    }

    public function getRedirectButtonName()
    {
        return $this->redirectButtonName;
    }

    public function setShowRedirectButton($value)
    {
        $this->showRedirectButton = $value;
        return $this;
    }

    public function getShowRedirectButton()
    {
        return $this->showRedirectButton;
    }

    public function setDefaultLanguage($value)
    {
        $this->defaultLanguage = $value;
        return $this;
    }

    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    public function setQuestions($value)
    {
        $this->questions = $value;
        return $this;
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    public function setFilterQuestions($value)
    {
        $this->filterQuestions = $value;
        return $this;
    }

    public function getFilterQuestions()
    {
        return $this->filterQuestions;
    }

    public function setStrictFilter($value)
    {
        $this->strictFilter = $value;
        return $this;
    }

    public function getStrictFilter()
    {
        return $this->strictFilter;
    }

}
