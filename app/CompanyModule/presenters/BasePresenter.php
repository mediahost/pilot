<?php

namespace CompanyModule;

use AppForms\StatusByCompanyUpdateForm;
use Model\Service\CompanyService,
    Model\Service\CandidateService,
    Model\Service\CvService;
use Nette\Application\UI\Multiplier;

/**
 * Class BasePresenter
 * @package CompanyModule
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class BasePresenter extends \BasePresenter
{

    /** @var  CompanyService */
    protected $company;

    /** @var  CandidateService */
    protected $candidates;

    /** @var  CvService */
    protected $cvs;

    /** @var \Model\Service\ChatService */
    protected $chatService;

    /**
     * @param CompanyService $service
     */
    public function injectCompany(CompanyService $service)
    {
        $this->company = $service;
    }

    /**
     * @param CompanyService $service
     */
    public function injectCandidates(CandidateService $service)
    {
        $this->candidates = $service;
    }

    /**
     * @param CvService $service
     */
    public function injectCvs(CvService $service)
    {
        $this->cvs = $service;
    }

    public function injectChatService(\Model\Service\ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Check if user is in role 'candidator'. If NOT, redirect to login page
     */
    public function startUp()
    {
        parent::startup();
        //Define new storage namespace, so user can be logged in Company & other sections
        $this->user->getStorage()->setNamespace('Company');
        $this->user->setAuthenticator($this->company);

        if (!$this->user->isAllowed("candidates", "access") && $this->name !== 'Company:Sign') {
            $this->redirect('Sign:in');
        }
    }

    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->unreadMessagesCount = $this->chatService->getUnreadCountByCompany($this->user->id);
        $this->template->lastChats = $this->chatService->findChatsByCompany($this->user->id, 5);
    }

    public function createComponentChatMessageForm()
    {
        return new \Nette\Application\UI\Multiplier($this->createChatMessageForm);
    }

    public function createChatMessageForm($id)
    {
        $form = new \AppForms\ChatMessageForm($this, $this->context->chat, $this->context->mail, $this->action == 'chat', $this->action == 'default');
        $form->setUserId($id);
        $form->setCompanyId($this->user->id);
        $form->setSender(\AppForms\ChatMessageForm::SENDER_COMPANY);
        $form->setGlobalSettingDestination(':Company:Homepage:settings');
        if ($this->action == 'default') {
            $form->setMessagesLink(':Company:Homepage:messages');
        }
        return $form;
    }

    public function createComponentHistChatMessageForm()
    {
        return new \Nette\Application\UI\Multiplier($this->createHistChatMessageForm);
    }

    public function createHistChatMessageForm($id)
    {
        $form = new \AppForms\ChatMessageForm($this, $this->context->chat, $this->context->mail, $this->action == 'chat', $this->action == 'default');
        $form->setUserId($id);
        $form->setCompanyId($this->user->id);
        $form->setSender(\AppForms\ChatMessageForm::SENDER_COMPANY);
        $form->setGlobalSettingDestination(':Company:Homepage:settings');
        if ($this->action == 'default') {
            $form->setMessagesLink(':Company:Homepage:messages');
        }
        $jobService = $this->context->jobs;
        $presenter = $this;
        $form->onSend = function () use ($id, $jobService, $presenter) {
            $jobUser = $jobService->getJobUserByJobAndUser($presenter->getParameter('id'), $id);
            if ($jobUser) {
                $jobService->addJobUserAction($jobUser->id, \Model\Mapper\Dibi\JobsDibiMapper::JOB_USER_ACTION_MESSAGE);
            }
        };
        return $form;
    }

    public function createComponentStatusUpdateForm()
    {
        return new Multiplier($this->createStatusUpdateForm);
    }

    public function createStatusUpdateForm($id)
    {
        $form = new StatusByCompanyUpdateForm($this, $this->context->jobs);
		if ($this->name == 'Company:Homepage' && $this->action == 'matched') {
			$form->setCandidateId($id);
			$form->setJobId($this->presenter->getParameter('id'));
		} else {
		    $form->setJobUserId($this->presenter->getParameter('id'));
		}
        return $form;
    }

}
