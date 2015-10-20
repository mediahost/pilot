<?php

namespace App\Model\Launchpad;

use Nette;
use Kdyby\Curl;
use App\Model\Entity\Launchpad\Interview\InterviewEntity;
use App\Model\Entity\Launchpad\Interview\InterviewLinkEntity;
use App\Model\Entity\Launchpad\Candidates\CandidateEntity;
use App\Model\Entity\Launchpad\Interview\ReviewInterviewLinkEntity;
use App\Model\Entity\Launchpad\Invites\InviteLinkEntity;

/**
 * LaunchpadManager
 */
class LaunchpadApi extends Nette\Object
{

    private $url;
    private $username;
    private $password;

    public function __construct($url, $username, $pass)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $pass;
    }

    private function getResponse($path, $params = [], $post = FALSE)
    {
        $loginData = $this->username . ":" . $this->password;
        $url = $this->url . $path;
        $request = new Curl\Request($url);
        $request->options = [
            'USERPWD' => $loginData,
            'SSL_VERIFYPEER' => FALSE,
        ];
        try {
            if ($post) {
                $responseRaw = $request->post($params);
            } else {
                $responseRaw = $request->get($params);
            }
            $response = json_decode($responseRaw->getResponse(), TRUE);
        } catch (Curl\CurlException $e) {
            $response = NULL;
        }
        return $response;
    }

    /**
     * HTTP Method: GET
     * Returns a list of interviews (opened and draft) under an account.
     * @param type $accountId
     * @return array
     */
    public function getInterviews($accountId = NULL)
    {
        $response = $this->getResponse("1/interviews", ["account_id" => $accountId]);
        $list = [];
        if (isset($response["response"]) && is_array($response["response"])) {
            foreach ($response["response"] as $key => $item) {
                $interview = new InterviewEntity;
                if (is_array($item)) {
                    $interview->convert($item);
                }
                $list[$key] = $interview;
            }
        }
        return $list;
    }

    /**
     * HTTP Method: GET
     * Returns the details of an Interview
     * @param type $interviewId
     * @param type $accountId
     * @return null|array
     */
    public function getInterview($interviewId, $accountId = NULL)
    {
        $response = $this->getResponse("1/interviews/{$interviewId}", ["account_id" => $accountId]);
        $interview = new InterviewEntity;
        if (isset($response["response"])) {
            $interview->convert($response["response"]);
        }
        return $interview;
    }

    /**
     * HTTP Method: GET
     * Returns the public link for an interview, identified by its Interview ID. Public links are used to invite anyone to take your video interview.
     * @param type $interviewId
     * @param type $accountId
     * @return \App\Model\Entity\Interview\InterviewLinkEntity
     */
    public function getInterviewLink($interviewId, $accountId = NULL)
    {
        $response = $this->getResponse("1/interviews/{$interviewId}/public_link", ["account_id" => $accountId]);
        $link = new InterviewLinkEntity;
        if (isset($response["response"])) {
            $link->convert($response["response"]);
        }
        return $link;
    }

    /**
     * HTTP Method: POST
     * Returns the URL of the video interview, where you can view the actual recordings for each interview question.
     * You will need to have both the candidate ID and the interview ID to be able to get the Review Interview link.
     * You can also use this URL to display the interview in an iframe.
     * You can also customize the look and feel of the page by specifying an external CSS that will be loaded when you access the link.
     * The external CSS is set using the css_url parameter.
     * Important Note: The interview URL returned is only valid for 8 hours.
     * After that, the link will no longer work and you will need to request for a new link.
     * This URL is not meant to be distributed/shared publicly.
     * @param type $interviewId
     * @param type $candidateId
     * @param type $cssUrl
     * @param type $accountId
     * @return \App\Model\Entity\Launchpad\Interview\ReviewInterviewLinkEntity
     */
    public function getReviewInterviewLink($interviewId, $candidateId, $cssUrl = NULL, $accountId = NULL)
    {
        $response = $this->getResponse("1/interviews/{$interviewId}/review_interview_link", [
            "candidate_id" => $candidateId,
            "css_url" => $cssUrl,
            "account_id" => $accountId,
                ], TRUE);
        $link = new ReviewInterviewLinkEntity;
        if (isset($response["response"])) {
            $link->convert($response["response"]);
        }
        return $link;
    }

    /**
     * HTTP Method: GET
     * Returns the details of a candidate. Please note that you can only get the details of a candidate that:
     *  - you created
     *  - has been invited to one of your interviews
     * @param type $candidateId
     * @param type $accountId
     */
    public function getCandidate($candidateId, $accountId = NULL)
    {
        $response = $this->getResponse("1/candidates/{$candidateId}", ["account_id" => $accountId]);
        if ($response === NULL) {
            return NULL;
        }
        $candidate = new CandidateEntity;
        if (isset($response["response"])) {
            $candidate->convert($response["response"]);
        }
        return $candidate;
    }

    /**
     * HTTP Method: POST
     * Creates a new candidate record.
     * The candidate email must be unique.
     * If an existing candidate record with the same email already exists, then that candidate's details
     * will be returned and no new records will be created.
     * @param CandidateEntity $candidate
     * @param type $accountId
     */
    public function setCandidate(CandidateEntity $candidate, $accountId = NULL)
    {
        $response = $this->getResponse("1/candidates", $candidate->toArray($accountId), TRUE);
        $newCandidate = new CandidateEntity;
        if (isset($response["response"])) {
            $newCandidate->convert($response["response"]);
//            if ($newCandidate->firstName != $candidate->firstName || $newCandidate->lastName != $candidate->lastName) {
//                $newCandidate->firstName = $candidate->firstName;
//                $newCandidate->lastName = $candidate->lastName;
//                $newCandidate = $this->updateCandidate($newCandidate, $accountId);
//            }
        }
        return $newCandidate;
    }

    private function updateCandidate(CandidateEntity $candidate, $accountId = NULL)
    {
        if ($candidate->candidateId !== NULL) {
            $response = $this->getResponse("1/candidates/{$candidate->candidateId}", $candidate->toArray($accountId), TRUE);
            $newCandidate = new CandidateEntity;
            if (isset($response["response"])) {
                $newCandidate->convert($response["response"]);
            }
            return $newCandidate;
        } else {
            return $candidate;
        }
    }

    public function getInviteLink($interviewId, $candidateId, $customInviteId = NULL, $sendEmail = FALSE, $accountId = NULL)
    {
        $response = $this->getResponse("1/interviews/{$interviewId}/seamless_login_invite", [
            "candidate_id" => $candidateId,
            "custom_invite_id" => $customInviteId,
            "send_email" => $sendEmail,
            "account_id" => $accountId,
                ], TRUE);
        $invite = new InviteLinkEntity;
        if (isset($response["response"])) {
            $invite->convert($response["response"]);
        }
        return $invite;
    }

}
