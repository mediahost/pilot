<?php

namespace FrontModule;

use \Nette\Application\UI\Form;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function startup()
	{
		parent::startup();
		if ($this->user->isLoggedIn()) {
			$this->redirect("Dashboard:");
		}
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->setLayout("homepage");
	}

	public function renderDefault()
	{
//        $accounts = array(
//            "login" => "pass",
//        );
//        foreach ($accounts as $login => $pass) {
//            $salt = \Model\Service\UserService::generateSalt();
//            $hash = \Model\Service\UserService::calculateHash($pass, $salt);
//            \Nette\Diagnostics\Debugger::barDump($login, "login");
//            \Nette\Diagnostics\Debugger::barDump($hash, "pass");
//            \Nette\Diagnostics\Debugger::barDump($salt, "salt");
//        }
	}

	/**
	 * Sign-in form factory.
	 * @return Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new \AppForms\SignInForm($this, $this->context->users);
		$form->setBacklink($this->getParameter('backlink'));
		return $form;
	}

    /**
     * @return Form
     */
    protected function createComponentGetInTouch()
    {
        return new \AppForms\GetInTouchForm2($this, $this->context->mail);
    }

    /**
     * Create account form factory.
     * @return Form
     */
    protected function createComponentCreateAccountForm2()
    {
		$form = new \AppForms\CreateAccountForm($this, $this->context->users, FALSE);
		$form->setSendText('Let\'s go »');
		$form->setSizes(NULL, NULL);
        return $form;
    }
	
    protected function createComponentCreateAccountForm3()
    {
		$form = new \AppForms\CreateAccountForm($this, $this->context->users, FALSE);
		$form->setSendText('Join now »');
		$form->setSizes(NULL, NULL);
        return $form;
    }

}
