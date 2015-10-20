<?php

namespace FrontModule;

/**
 * Inner Presenter - For inner pages without layout - only for colorbox interpretation
 *
 * @author Petr Poupě
 */
class InnerPresenter extends BasePresenter
{

    private $cvId = NULL;

    /** @var \App\Model\Launchpad\LaunchpadApi */
    public $launchpad;

    /** @var CompanyService */
    private $company;

    /**
     * @param CompanyService $service
     */
    public function injectCompany(\Model\Service\CompanyService $service)
    {
        $this->company = $service;
    }
    
    public function injectLaunchpad(\App\Model\Launchpad\LaunchpadApi $launchpad) {
        $this->launchpad = $launchpad;
    }
    
    public function startup()
    {
        $this->userService = $this->context->users;
        parent::startup();
    }
    
    /**
     * Select type of new cv - clone or add
     */
    public function actionAddCv($id)
    {
        $this->cvId = $id;
    }

    protected function createComponentAddCv()
    {
        return new \AppForms\SelectNewCvTypeForm($this, $this->context->cv, $this->cvId);
    }

    /**
     * Get In Touch
     */
    public function actionGetInTouch()
    {
        
    }

    protected function createComponentGetInTouch()
    {
        return new \AppForms\GetInTouchForm($this, $this->context->mail);
    }

    /**
     * Send CV by email
     */
    public function actionSendCvByMail($cv)
    {
        $this["sendCvByMail"]->setDefaults($cv);
    }

    protected function createComponentSendCvByMail()
    {
        return new \AppForms\SendCvByMailForm($this);
    }

    /**
     * Apply Job offer
     */
    public function actionJobApply($jobid)
    {
        if (!$this->user->isLoggedIn()) {
            $this->error();
        }
        $job = $this->context->jobs->findById($jobid);
        /* @var $cv \Model\Entity\CvEntity */
        $cv = $this->context->cv->getDefaultCv($this->user->id);
        if (!$cv->isCompleted()) {
            $this->error();
        }
		if ($job) {
			$company = $this->company->findUser($job->company_id);
			$user = $this->userService->find($this->user->id);
            $mail = $this->context->mail->create($this->lang);
            $mail->setTo($company->email);
            $mail->selectFrom(\Model\Service\MailFactory::FROM_NOREPLY);
            $mail->selectMail(\Model\Service\MailFactory::MAIL_APPLY_JOB, array(
                'company' => $company->username,
                'candidate' => $cv->getFullName(),
                'job' => $job->name,
                'link' => $this->link('//:Profile:Homepage:', $user->profile_token),
            ));
            $mail->send();
		}
        $this->context->jobs->apply($jobid, $this->user->id);
        $this->template->job = $job;
        $this->template->cv = $this->context->cv->getDefaultCv($this->user->id);
    }

    protected function createComponentJobApply()
    {
        $cvs = $this->context->cv->findUsersCv($this->user->getId());
        $cvsIds = array();
        foreach ($cvs as $cv) {
            $cvsIds[$cv->id] = $cv->name;
        }
        return new \AppForms\JobApplyForm($this, $this->context->jobapplys, $this->context->mail, $this->context->cv, $cvsIds);
    }

    /**
     * Edit forum
     */
    public function actionEditForum($cid, $fid = NULL)
    {
        $category = $this->context->forum->getCategory($cid);
        if ($category->id === NULL) {
            $this->flashMessage("This category wasn't find.", "warning");
            $this->redirect("Forum:");
        } else {
            $forum = $this->context->forum->getForum($fid);
            if ($forum->id !== NULL) {
                if ($forum->categoryId !== $category->id) {
                    $this->flashMessage("This forum wasn't find for this category.", "warning");
                    $this->redirect("Forum:");
                }
            } else {
                $forum->categoryId = $cid;
            }
            $this["editForum"]->setDefaults($forum);
        }
        $this->template->category = $category;
        $this->template->forum = $forum;
    }

    protected function createComponentEditForum()
    {
        return new \AppForms\ForumForumForm($this, $this->context->forum);
    }

    /**
     * Edit Category
     */
    public function actionEditForumCategory($cid = NULL)
    {
        $category = $this->context->forum->getCategory($cid);
        if ($category->id === NULL) {
            $category->lang = $this->lang;
        }
        $this["editForumCategory"]->setDefaults($category);
        $this->template->category = $category;
    }

    protected function createComponentEditForumCategory()
    {
        return new \AppForms\ForumCategoryForm($this, $this->context->forum, TRUE);
    }

    /**
     * Change CV Template
     */
    public function actionChangeCvTemplate($cv)
    {
        $templateName = $this->context->cv->getCv($cv)->templateName;
        $this["changeCvTemplate"]->setDefaults($cv, $templateName);
    }

    protected function createComponentChangeCvTemplate()
    {
        return new \AppForms\ChangeCvTemplateForm($this, $this->context->cv);
    }
    
    public function createComponentChatMessageForm()
    {
        $job = $this->context->jobs->findById($this->getParameter('jobid'));
        $form = new \AppForms\ChatMessageForm($this, $this->context->chat, $this->context->mail, FALSE, TRUE);
        $form->getElementPrototype()
            ->class = 'styled innerPage';
        $form->setUserId($this->user->id);
        $form->setJob($job);
        $form->setCompanyId($job->company_id);
        $form->setSender(\AppForms\ChatMessageForm::SENDER_USER);
        $form->setRedirect(':Front:Jobs:show', $job->code);
        $form->setMessagesLink(':Front:Dashboard:messages');
        return $form;
    }
    
    public function actionVideoProfile($userId)
    {
        $user = $this->userService->find($userId);
        if (!$user) {
            $this->error();
        }
        
        $candidate = new \App\Model\Entity\Launchpad\Candidates\CandidateEntity(array(
            'custom_candidate_id' => $user->id,
            'email' => $user->mail,
        ));
        $interviewId = 8327;
        $createdCandidate = $this->launchpad->setCandidate($candidate);
        $cssUrl = $this->template->baseUri . '/css/launchpad-review-style.css';
        $reviewLink = $this->launchpad->getReviewInterviewLink($interviewId, $createdCandidate->getCandidateId(), $cssUrl)->getLink()['url'];
        $reviewLink = preg_replace('/^http:/', 'https:', $reviewLink);
        if (!$reviewLink) {
            $user->launchpadVideoUrl = '';
            $this->userService->save($user);
        }
        $this->template->link = $reviewLink;
    }

}
