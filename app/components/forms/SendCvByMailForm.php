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
class SendCvByMailForm extends AppForms
{

    /** @var MailService */
    private $mail;

    public function __construct(Presenter $presenter)
    {
        parent::__construct(get_class($this), $presenter);
    }
    
    public function setDefaults($cv) {
        $form = $this->getComponent($this->name);
        $form->setDefaults(array(
            'cv' => (int) $cv,
        ));
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled innerPage";

        $this->form->addHidden('cv');
        $this->form->addText('mail', 'Send to')
                ->setEmptyValue("@")
                ->addRule(Form::EMAIL, 'Entered value is not email!');
        $this->form->addTextArea('text', 'E-mail text')
				->setDefaultValue('Hi, look at my new cv from pilotincommands.com');

        $this->form->addSubmit('send', 'Send')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $this->presenter->redirect("Pdf:cv", array(
            'cv' => $form->values->cv,
            'send' => $form->values->mail,
            'text' => $form->values->text,
        ));
    }

}
