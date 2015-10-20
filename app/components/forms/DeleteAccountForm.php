<?php

namespace AppForms;

use Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService;

/**
 * Delete Account Form
 *
 * @author Petr Poupě
 */
class DeleteAccountForm extends AppForms
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

        $this->form->addSubmit('yes', 'Yes')
                ->setAttribute("class", "positive button");
        $this->form->addSubmit('no', 'Not today')
                ->setAttribute("class", "negative button");

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        if ($form['yes']->submittedBy) {
            $user = $this->service->find($this->user->getId());
            $this->service->delete($user);
            $this->flashMessage("Your account was deleted. Good bye!");
            $this->presenter->redirect("Sign:out");
        };
        $this->presenter->redirect("Account:");
    }

}

?>
