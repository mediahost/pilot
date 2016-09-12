<?php

namespace FrontModule;

use \Nette\Application\UI\Form;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

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
        return new \AppForms\GetInTouchForm($this, $this->context->mail);
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

	public function createComponentContact()
	{
	    $form = new Form();
		$form->addText('name');
		$form->addText('email');
		$form->addTextArea('message');
		$form->addSubmit('send');
		$form->onSuccess[] = [$this, 'processContact'];
		return $form;

	}

	public function processContact(Form $form)
	{
		$values = $form->values;
		/** @var IMailer $mailer */
		$mailer = $this->context->getByType('Nette\Mail\IMailer');
		$message = new Message();
		$message->addTo('kapicak@gmail.com');
		$message->setFrom('pilotincommand@info.com');
		$message->setSubject('Contact');
		$message->setBody(
			"Name: {$values->name}".PHP_EOL.
			"Email: {$values->email}".PHP_EOL.
			"Message: {$values->message}"
		);
		$mailer->send($message);

		$this->flashMessage('Thanks for your submission, we will be in touch shortly.');
		$this->redirect('this#contact');
	}

}
