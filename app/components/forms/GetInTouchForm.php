<?php

namespace AppForms;

use \Nette\Application\UI\Form,
	Nette\Application\UI\Presenter,
	Model\Service\MailService;
use Nette\Diagnostics\Debugger;

/**
 * GetInTouch Form
 *
 * @author Petr Poupě
 */
class GetInTouchForm extends AppForms
{

	/** @var MailService */
	private $mail;

	public function __construct(Presenter $presenter, MailService $mail)
	{
		parent::__construct(get_class($this), $presenter, FALSE);

		$this->mail = $mail;
	}

	protected function createComponent($name)
	{
		$this->form->addText('name', 'Your name')
						->addRule(Form::FILLED, "Please enter your name!")
						->getControlPrototype()->placeholder = "Enter your name";
		$this->form->addText('sender', 'Your email')
			->addRule(Form::FILLED, "Please enter your email")
			->addRule(Form::EMAIL, "Entered value is not email!")
			->getControlPrototype()->placeholder = "Email your email";
		$this->form->addText('subject', 'Subject')
			->getControlPrototype()->placeholder = "Enter subject";
		$this->form->addTextArea('message', 'Message')
						->addRule(Form::FILLED, "Please don't send us empty messages!")
						->getControlPrototype()->placeholder = "Add your Message";

		$this->form->addSubmit('send', 'Submit')
						->getControlPrototype()->class = "button";

		$this->form->onSuccess[] = $this->onSuccess;

		return $this->form;
	}

	public function onSuccess(Form $form)
	{
		$mail = $this->mail->create($this->lang);
		$mail->selectMail(\Model\Service\MailFactory::PRIVATE_MAIL_GET_IN_TOUCH, array(
			'from' => $form->values->sender,
			'name' => $form->values->name,
			'subject' => $form->values->subject,
			'message' => $form->values->message,
			'feelings' => NULL,
		));
		$mail->send();
		$this->presenter->flashMessage("Your message was send", "success");
		$this->presenter->redirect("Homepage:");
	}

}
