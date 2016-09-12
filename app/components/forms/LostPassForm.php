<?php

namespace AppForms;

use Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService;

/**
 * LostPass Form
 *
 * @author Petr Poupě
 */
class LostPassForm extends AppForms
{

    /** @var \Model\Service\UserService */
    private $service;

    public function __construct(Presenter $presenter, UserService $service)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->service = $service;
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "front";
        
        $this->form->addText('email', 'E-mail', 40)
                ->setEmptyValue("@")
                ->addRule(Form::EMAIL, "Enter valid e-mail")
                ->addRule(Form::FILLED, "Must be filled");

        $this->form->addSubmit('send', 'Send Password')
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        if ($this->service->resetAppPasswordByMail($form->values->email, TRUE, $this->lang)) {
            $this->presenter->flashMessage("Your password was send to your mail", "success");
            $this->presenter->redirect("Sign:in");
        } else {
            $this->form->addError($this->translator->translate("This email not found"));
        }
    }

}

?>
