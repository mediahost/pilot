<?php

namespace CompanyProfileModule;

class HomepagePresenter extends \BasePresenter
{
    
    /** @var \Model\Service\CompanyService */
    protected $companyService;
    
    /** @var \Model\Entity\Company\UserEntity */
    protected $company;
    
    /** @var \Model\Service\JobService */
    protected $jobService;
    
    /** @var \Model\Service\CvService */
    protected $cvService;
    
    /** @var \Model\Service\ChatService */
    protected $chatService;
    
    public function injectService(\Model\Service\CompanyService $companyService,
        \Model\Service\JobService $jobService,
        \Model\Service\CvService $cvService,
        \Model\Service\ChatService $chatService)
    {
        $this->companyService = $companyService;
        $this->jobService = $jobService;
        $this->cvService = $cvService;
        $this->chatService = $chatService;
    }
    
    public function formatLayoutTemplateFiles()
    {
        $path = realpath(__DIR__ . '/../../FrontModule/templates/@layout.latte');
        return array($path);
    }
    
    public function actionDefault($slug)
    {
        $this->company = $this->companyService->findUserBySlug($slug);
        if (!$this->company) {
            $this->error();
        }
    }
    
    public function renderDefault($slug)
    {
        $this->template->company = $this->company;
        $this->template->jobs = $this->jobService->findBy('company_id', $this->company->id);
        if ($this->user->isLoggedIn()) {
            $this->template->cv = $this->template->defaultCv = $this->cvService->getDefaultCv($this->user->id);
            $this->template->unreadMessagesCount = $this->chatService->getUnreadCountByUser($this->user->id);
            $this->template->lastChats = $this->chatService->findChatsByUser($this->user->id, 5);
        } elseif ($this->user->isCompany()) {
            $companyId = $this->user->getCompanyIdentity()->id;
            $this->template->unreadMessagesCount = $this->chatService->getUnreadCountByCompany($companyId);
            $this->template->lastChats = $this->chatService->findChatsByCompany($companyId, 5);
        }
        $this->template->backlink = $this->storeRequest();
    }
    
    /**
     * Sign-in form factory.
     * @return Form
     */
    protected function createComponentSignInForm()
    {
        $form = new \AppForms\SignInForm($this, $this->context->users);
        $form->setBacklink($this->storeRequest());
        return $form;
    }
    
}
