<?php

namespace AppForms;

use Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService;

/**
 * VerifyAccount Form
 *
 * @author Petr Poupě
 */
class VerifyAccountForm extends AppForms
{

    /** @var \Model\Service\UserService */
    private $service;

    public function __construct(Presenter $presenter, UserService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setDefaults($code)
    {
        parent::setDefaultValues(array(
            'code' => $code,
        ));
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "front";
        
        $this->form->addText('code', 'Code', 40)
                ->addRule(Form::FILLED, "Fill code");

        $this->form->addSubmit('send', 'Verify')
                ->setAttribute("class", "button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $this->presenter->redirect("this", array("code" => $form->values->code));
    }

}

?>
