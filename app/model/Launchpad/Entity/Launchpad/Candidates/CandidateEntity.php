<?php

namespace App\Model\Entity\Launchpad\Candidates;

/**
 * CandidateEntity
 *
 * @author Petr Poupě
 * @property string $candidateId
 * @property string $customCandidateId
 * @property string $email
 * @property string $firstName
 * @property string $lastName
 */
class CandidateEntity extends \App\Model\Entity\BaseEntity
{

    /** @var string */
    private $candidateId;

    /** @var string */
    private $customCandidateId;

    /** @var string */
    private $email;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    public function __construct($candidate = [])
    {
        $this->convert($candidate);
    }

    public function convert($candidate)
    {
        if (is_array($candidate)) {
            if (array_key_exists("candidate_id", $candidate)) {
                $this->setCandidateId($candidate["candidate_id"]);
            }
            if (array_key_exists("custom_candidate_id", $candidate)) {
                $this->setCustomCandidateId($candidate["custom_candidate_id"]);
            }
            if (array_key_exists("email", $candidate)) {
                $this->setEmail($candidate["email"]);
            }
            if (array_key_exists("first_name", $candidate)) {
                $this->setFirstName($candidate["first_name"]);
            }
            if (array_key_exists("last_name", $candidate)) {
                $this->setLastName($candidate["last_name"]);
            }
        }
    }

    public function toArray($accountId = NULL)
    {
        $array = [
            "email" => $this->email,
            "custom_candidate_id" => $this->customCandidateId,
            "account_id" => $accountId,
            "first_name" => $this->firstName,
            "last_name" => $this->lastName,
        ];
        return $array;
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

    public function setFirstName($value)
    {
        $this->firstName = $value;
        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($value)
    {
        $this->lastName = $value;
        return $this;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

}
