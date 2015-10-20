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
class GetInTouchForm extends AppForms
{

    private $feelings;

    /** @var MailService */
    private $mail;

    public function __construct(Presenter $presenter, MailService $mail)
    {
        parent::__construct(get_class($this), $presenter);

        $this->mail = $mail;

        $this->feelings = array(
            1 => "I'm unhappy",
            2 => "I'm okay",
            3 => "I'm happy!",
        );
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled innerPage";

//        $this->form->addText('name', 'Your name')
//                ->addRule(Form::FILLED, "Please enter your name!");
//        $this->form->addText('sender', 'Your email')
//                ->setEmptyValue("@")
//                ->addRule(Form::EMAIL, "Entered value is not email!");
        $this->form->addText('subject', 'Subject')
                ->addRule(Form::FILLED, "Please enter subject!");
        $this->form->addTextArea('message', 'Message')
                ->addRule(Form::FILLED, "Please don't send us empty messages!");
        $this->form->addRadioList('feelings', "How are you feeling?", $this->feelings);
        $this->form['feelings']->getControlPrototype()->class = "radioFeelings";
        $this->form['feelings']->getSeparatorPrototype()->setName(NULL);

        $this->form->addSubmit('send', 'Send')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $mail = $this->mail->create($this->lang);
        $feelings = array_merge(
                array(NULL => "not inserted"), $this->feelings
        );
		$sender = NULL;
		$senderName = NULL;
		$identity = $this->user->identity;
		if ($identity) {
			$senderName = $identity->first_name . ' ' . $identity->last_name;
			$sender = $identity->mail;
		}
        $mail->selectMail(\Model\Service\MailFactory::PRIVATE_MAIL_GET_IN_TOUCH, array(
            'from' => $sender,
            'name' => $senderName,
            'subject' => $form->values->subject,
            'message' => $form->values->message,
            'feelings' => array_key_exists($form->values->feelings, $feelings) ? $feelings[$form->values->feelings] : $feelings[NULL],
        ));
        $mail->send();
        $this->presenter->redirect("Homepage:");
    }

}
