<?php

namespace ServiceModule;

class CvIsCompletedRecalculatePresenter extends BasePresenter
{
    
    /** @var \Model\Service\CvService */
    protected $cvService;
    
    public function injectCvService(\Model\Service\CvService $cvService)
    {
        $this->cvService = $cvService;
    }
    
    public function startup()
    {
        parent::startup();
        $this->checkAccess("service", "access");
    }
    
    public function actionDefault()
    {
        foreach ($this->cvService->getAll() as $cv) {
            $this->cvService->save($cv);
            if ($cv->photo && $cv->showPhoto) {
                \AppForms\AppForms::copyImage('foto/original/cvImages/'.$cv->photo, 'photo', $cv->userId);
            }
        }
        $this->sendResponse(new \Nette\Application\Responses\TextResponse('ok'));
    }
    
}
