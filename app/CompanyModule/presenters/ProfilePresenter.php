<?php

namespace CompanyModule;

class ProfilePresenter extends BasePresenter
{
    
    /** @var \Model\Entity\CandidateEntity */
    protected $candidateEntity;
    
    public function actionShow($id)
    {
        $candidates = $this->candidates->getCandidates(array(
            'id' => $id
        ));
        if (count($candidates)==0) {
            $this->error();
        }
        $this->candidateEntity = $candidates[$id];
        $this->candidateEntity->cv = $this->cvs->getCv($this->candidateEntity->cvId);
        if (!$this->candidateEntity->cv->isCompleted()) {
            $this->error();
        }
    }
    
    public function renderShow($id)
    {
        $this->template->candidate = $this->candidateEntity;
        $this->extendTemplate();
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
    
}
