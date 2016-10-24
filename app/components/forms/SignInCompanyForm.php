<?php

namespace AppForms;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

class SignInCompanyForm extends AppForms
{

	private $company;

    public function __construct(Presenter $presenter)
    {
        parent::__construct(get_class($this), $presenter, FALSE);
    }

	public function setStyled()
	{
		$this->form->getElementPrototype()->addClass('front');
	}

	public function setCompany($company)
	{
		$this->company = $company;
	}

    protected function createComponent($name)
    {
        $this->form->addText('username', 'E-mail')
                ->setRequired('Please enter your username.')
		->setAttribute("placeholder", 'Username');

        $this->form->addPassword('password', 'Password')
                ->setRequired('Please enter your password.')
		->setAttribute("placeholder", 'Password');

        $this->form->addSubmit('send', 'Sign in')
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
		$values = $form->values;
		try {
			$this->user->getStorage()->setNamespace('Company');
			$this->user->setAuthenticator($this->company);

			$this->user->login($values->username, $values->password);
			$this->user->setExpiration('20 minutes', TRUE);
			$this->presenter->flashMessage('Logged in!', 'success');
			$this->presenter->redirect(':Company:Homepage:jobs');
		} catch (AuthenticationException $e) {
			$form->addError($this->translator->translate($e->getMessage()));
		}
    }

}
