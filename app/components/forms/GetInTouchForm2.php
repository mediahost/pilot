<?php

namespace AppForms;

use \Nette\Application\UI\Form,
	Nette\Application\UI\Presenter,
	Model\Service\MailService;

/**
 * GetInTouch Form
 *
 * @author Petr Poupě
 */
class GetInTouchForm2 extends AppForms
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
						->getControlPrototype()->placeholder = "Your Name";
		$this->form->addText('sender', 'Your email')
						->addRule(Form::EMAIL, "Entered value is not email!")
						->getControlPrototype()->placeholder = "Email Address";
		$this->form->addTextArea('message', 'Message')
						->addRule(Form::FILLED, "Please don't send us empty messages!")
						->getControlPrototype()->placeholder = "Your Message";

		$this->form->addSubmit('send', 'Submit Enquiry »')
						->getControlPrototype()->class = "button";

		$this->form->onSuccess[] = $this->onSuccess;

		return $this->form;
	}

	public function onSuccess(Form $form)
	{
		$mail = $this->mail->create($this->lang);
		$sender = $form->values->sender;
		$senderName = $form->values->name;
		$mail->selectMail(\Model\Service\MailFactory::PRIVATE_MAIL_GET_IN_TOUCH, array(
			'from' => $sender,
			'name' => $senderName,
			'subject' => 'Get In Touch',
			'message' => $form->values->message,
			'feelings' => NULL,
		));
		$mail->send();
		$this->presenter->flashMessage("Your message was send", "success");
		$this->presenter->redirect("Homepage:");
	}

}
