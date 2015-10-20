<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService,
    Nette\Security\AuthenticationException;

/**
 * SignIn Form
 *
 * @author Petr Poupě
 */
class SignInForm extends AppForms
{

    /** @var string */
    private $backlink;

    /** @var \Model\Service\UserService */
    private $service;

    public function __construct(Presenter $presenter, UserService $userService)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->service = $userService;
    }

    public function setBacklink($backlink)
    {
        $this->backlink = $backlink;
    }
    
    public function setStyled()
    {
        $this->form->getElementPrototype()->addClass('front');
    }

    protected function createComponent($name)
    {
        $this->form->addText('username', 'E-mail')
                ->setRequired('Please enter your username.')
		->setAttribute("placeholder", 'E-mail');

        $this->form->addPassword('password', 'Password')
                ->setRequired('Please enter your password.')
		->setAttribute("placeholder", 'Password');

        $this->form->addCheckbox('remember', 'Keep me signed in');

        $this->form->addSubmit('send', 'Sign in')
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $sign = new \Model\Entity\SignInEntity;
        $sign->login = $form->values->username;
        $sign->password = $form->values->password;
        $sign->remember = $form->values->remember;
        
        try {
            $auth = $this->service->checkAppAuth($sign);
            $remember = $sign->remember;
            unset($sign);
            $user = $this->service->find($auth->userId);
            
            $this->service->checkVerification($auth, $this->presenter);
            $this->service->sign($user, $this->user, $remember);
            $this->presenter->flashMessage('Login was successful', 'success');
            $this->presenter->restoreRequest($this->backlink);
            $this->presenter->redirect(':Front:Dashboard:');
        } catch (AuthenticationException $e) {
            $this->presenter->flashMessage($e->getMessage(), "warning");
            switch ($e->getCode()) {
                case \Nette\Security\IAuthenticator::NOT_APPROVED:
                    $this->presenter->redirect(":Front:Sign:verify");
                    break;
                default:
                    $this->presenter->redirect(":Front:Sign:in");
                    break;
            }
        }
    }
    
    public function render()
    {
        $this->template->createLink = $this->presenter->link(":Front:Homepage:default#sign");
        $this->template->lostLink = $this->presenter->link(":Front:Sign:lostPass");
        parent::render();
    }
    
    public function renderInline()
    {
		$this->setTemplateName('signInFormInline');
        $this->render();
    }

}
