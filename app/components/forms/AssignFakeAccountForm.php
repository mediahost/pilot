<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\UserService;

/**
 * Assign Fake Account Form
 *
 * @author Petr Poupě
 */
class AssignFakeAccountForm extends AppForms
{

    /** @var UserService */
    private $service;
    
    private $authKey = "fake@account.test";

    public function __construct(Presenter $presenter, UserService $service)
    {
        parent::__construct(get_class($this), $presenter);

        $this->service = $service;
    }

    public function setDefaults()
    {
        $auth = $this->service->findAuth($this->authKey);
        parent::setDefaultValues(array(
            'user' => $auth->userId,
        ));
    }

    protected function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
	
	$users = $this->service->getUsers();
        $this->form->addSelect('user', 'User', $users)
                ->setAttribute("class", "select2me");

        $this->form->addSubmit('send', 'Save');

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $auth = $this->service->findAuth($this->authKey);
		if ($auth->id !== NULL) {
			$auth->userId = $form->values->user;
			$this->service->saveAuth($auth);
		}
    }

}
