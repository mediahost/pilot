<?php

namespace CompanyModule;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

/**
 * Class SignPresenter
 * @package CompanyModule
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class SignPresenter extends BasePresenter
{

    /**
     * Disable access to Login form for logged in user
     */
    public function actionIn()
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }
    }

    /**
     * Builds login form
     * @return Form
     */
    protected function createComponentSignInForm()
    {
        $form = new Form();
        $form->getElementPrototype()->class = "front";
        $form->addText('username', 'Username')
                ->setRequired("Please enter your username.");
        $form->addPassword('password', 'Password')
                ->setRequired("Please enter your password.");
        $form->addProtection('Login form was expired, please submit it again');
        $form->addSubmit('submit', 'Login');
        $form->onSuccess[] = $this->login;

        return $form;
    }

    /**
     * Process login form and try to login user
     *
     * @param Form $form
     */
    public function login(Form $form)
    {
        $values = $form->values;
        try {
            $this->user->login($values->username, $values->password);
            $this->user->setExpiration('20 minutes', TRUE);
            $this->flashMessage('Logged in!', 'success');
            $this->redirect('Homepage:jobs');
        } catch (AuthenticationException $e) {
            $form->addError($this->translator->translate($e->getMessage()));
        }
    }

    /**
     * Logs user out - ONLY from Candidates section!
     */
    public function actionOut()
    {
        $this->user->logout(true);
        $this->flashMessage('You have benn logged out', 'success');
        $this->redirect(':Front:Homepage:');
    }

}
