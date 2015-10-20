<?php

namespace ProfileModule;

class HomepagePresenter extends BasePresenter
{
    
    /** @var \Model\Service\CandidateService */
    protected $candidateService;
    
    /** @var \Model\Service\CvService */
    protected $cvService;
    
    /** @var \Model\Service\UserService */
    protected $userService;
    
    public function injectServices(\Model\Service\CandidateService $candidateService, \Model\Service\CvService $cvService)
    {
        $this->candidateService = $candidateService;
        $this->cvService = $cvService;
        $this->userService = $this->context->users;
    }
    
    public function renderDefault($token)
    {
        $user = $this->userService->findByToken($token);
        if (!$user->id && $this->user->isLoggedIn() && ($this->user->isInRole('admin') || $this->user->isInRole('superadmin'))) {
            $user = $this->userService->find($token);
        } elseif ($user->id && !$user->is_profile_public) {
            $this->error();
        }
        if (!$user->id) {
            $this->error();
        }
        
        $candidates = $this->candidateService->getCandidates(array(
            'id' => $user->id
        ));
        if (count($candidates) == 0) {
            $this->template->isCompleted = FALSE;
            return;
        }
        
        $candidateEntity = $candidates[$user->id];
        $candidateEntity->cv = $this->cvService->getCv($candidateEntity->cvId);
        
        if (!$candidateEntity->cv->isCompleted()) {
            $this->template->isCompleted = FALSE;
        } else {
            $this->template->isCompleted = TRUE;
            $this->template->candidate = $candidateEntity;
            $this->template->userEntity = $user;
            $this->extendTemplate();
        }

    }
    
}
