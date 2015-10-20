<?php

namespace AppForms;

use Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService;

/**
 * Insert Registration Mail Form
 *
 * @author Petr Poupě
 */
class InsertRegistrationMailForm extends AppForms
{

    private $backlink;

    /** @var UserService */
    private $service;

    public function __construct(Presenter $presenter, UserService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "front";
        
        $this->form->addText('mail', 'Mail', 50)
                ->setEmptyValue("@")
                ->addRule(Form::EMAIL, "Please fill valid e-mail")
                ->addRule(Form::FILLED, "Must be filled");

        $this->form->addSubmit('send', 'Insert')
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $this->presenter->redirect("this", array("mail" => $form->values->mail));
    }
    
    public function setBacklink($backlink)
    {
        $this->backlink = $backlink;
    }

}

?>
