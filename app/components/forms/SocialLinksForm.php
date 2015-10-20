<?php

namespace AppForms;

class SocialLinksForm extends AppForms
{
    
    /** @var \Model\Service\UserService */
    protected $userService;
    
    /** @var \Model\Entity\UserEntity */
    protected $user;
    
    public function __construct(\Nette\Application\UI\Presenter $presenter, \Model\Service\UserService $userService)
    {
        $this->userService = $userService;
        parent::__construct('socialLinksForm', $presenter, FALSE);
    }
    
    public function setUserEntity(\Model\Entity\UserEntity $user)
    {
        $this->user = $user;
    }
    
    protected function createComponent($name)
    {
        $this->form->addText('url_github', 'Github profile')
            ->setDefaultValue($this->user->url_github)
            ->addCondition(\Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::URL);
        $this->form->addText('url_stackoverflow', 'StackOverflow profile')
            ->setDefaultValue($this->user->url_stackoverflow)
            ->addCondition(\Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::URL);
        $this->form->addText('url_linkedin', 'LinkedIn profile')
            ->setDefaultValue($this->user->url_linkedin)
            ->addCondition(\Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::URL);
        $this->form->addText('url_facebook', 'Facebook')
            ->setDefaultValue($this->user->url_facebook)
            ->addCondition(\Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::URL);
        $this->form->addText('url_twitter', 'Twitter profile')
            ->setDefaultValue($this->user->url_twitter)
            ->addCondition(\Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::URL);
        $this->form->addSubmit('save', 'Save');
                
        $this->form->onSuccess[] = $this->processForm;
        
        return $this->form;
    }
    
    public function processForm(\Nette\Application\UI\Form $form)
    {
        $values = $form->values;
        foreach ($values as $key => $value) {
            if (!empty($value)) {
                if (!\Nette\Utils\Validators::isUrl($value) || \Nette\Utils\Validators::isUrl("http://$value")) {
                    $values->{$key} = "http://$value";
                }
            }
            $this->user->{$key} = $values->{$key};
        }
        $this->user->url_github = $values->url_github;
        $this->user->url_stackoverflow = $values->url_stackoverflow;
        $this->user->url_linkedin = $values->url_linkedin;
        $this->user->url_facebook = $values->url_facebook;
        $this->user->url_twitter = $values->url_twitter;
        
        $this->userService->save($this->user);
        $this->presenter->redirect('this');
    }
    
}
