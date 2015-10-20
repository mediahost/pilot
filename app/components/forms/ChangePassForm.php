<?php

namespace AppForms;

use Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService;

/**
 * ChangePass Form
 *
 * @author Petr Poupě
 */
class ChangePassForm extends AppForms
{

    /** @var \Model\Service\UserService */
    private $service;

    public function __construct(Presenter $presenter, UserService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "front";
        
        $this->form->addPassword('old', "Old password");
        
        $this->form->addPassword('password', "New password")
                ->setRequired("Insert your password")
                ->addRule(Form::MIN_LENGTH, "Password must have %d characters at least", 3);
        $this->form->addPassword('password2', "New password again")
                ->setRequired("Retype your password")
                ->addConditionOn($this->form['password'], Form::FILLED)
                ->addRule(Form::EQUAL, 'Passwords must be same', $this->form['password']);

        $this->form->addSubmit('send', 'Save Password')
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        if ($this->service->setNewAppPassword($this->user->getId(), $form->values->old, $form->values->password, TRUE, $this->lang)) {
            $this->presenter->flashMessage("Your password was changed.", "success");
        } else {
            $this->presenter->flashMessage("Old password is incorrect", "warning");
        }
        $this->redirect("this");
    }

}

?>
