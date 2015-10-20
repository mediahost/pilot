<?php

namespace AppForms;

class ProfileTokenForm extends AppForms
{
    
    /** @var \Model\Service\UserService */
    protected $userService;
    
    /** @var \Model\Entity\UserEntity */
    protected $userEntity;
    
    public function __construct(\Nette\Application\UI\Presenter $presenter, \Model\Service\UserService $service)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->userService = $service;
        $this->userEntity = $service->find($this->user->id);
    }
    
    public function createComponent($name)
    {
        $this->form->addText('profile_token')
            ->setDefaultValue($this->userEntity->profile_token);
        $this->form->addSubmit('save', 'Save');
        
        $this->form->onValidate[] = $this->validateForm;
        $this->form->onSuccess[] = $this->processForm;
        
        return $this->form;
    }
    
    public function validateForm(\Nette\Application\UI\Form $form)
    {
        $token = \Nette\Utils\Strings::webalize($form->values->profile_token);
        if (empty($token)) {
            $this->form->addError('Profile url can not be empty.');
            return;
        }
        if (!$this->userService->isUniqeProfileToken($token, $this->user->id)) {
            $this->form->addError('This profile url is already taken, choose another.');
        }
        if (is_numeric($token)) {
            $this->form->addError('Profile url can not be number, choose another.');
        }
    }
    
    public function processForm(\Nette\Application\UI\Form $form)
    {
        $token = \Nette\Utils\Strings::webalize($form->values->profile_token);
        $this->userEntity->profile_token = $token;
        $this->userService->save($this->userEntity);
        $this->presenter->redirect('this');
    }
    
}
