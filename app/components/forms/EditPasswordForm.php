<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService,
    Model\Service\MailService;

/**
 * Edit Password Form
 *
 * @author Stephen Monaghan
 */
class EditPasswordForm extends AppForms
{

    /** @var UserService */
    private $service;

    /** @var MailService */
    private $mail;

    public function __construct(Presenter $presenter, UserService $service, MailService $mail)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
        $this->mail = $mail;
    }

    public function setDefaults($login)
    {
        parent::setDefaultValues(array(
            'login' => $login,
        ));
    }

    protected function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);

        $this->form->addText('login', 'Login')
                ->setAttribute("readonly");
        $this->form->addText('password', 'Password')
                ->addRule(Form::FILLED, "Please enter a password");

        $this->form->addSubmit('send', 'Update')
                        ->getControlPrototype()->class = "button";

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $auth = $this->service->findAuth($form->values->login);
        $this->service->setNewAppPassword($auth->userId, FALSE, $form->values->password, TRUE, $this->lang);
        $this->presenter->flashMessage("Password updated and mail sent to user", 'success');
        $this->presenter->redirect("Users:");
    }

}

?>
