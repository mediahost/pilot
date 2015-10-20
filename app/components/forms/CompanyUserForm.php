<?php

namespace AppForms;

use Model\Entity\Company\UserEntity;
use Model\Service\CompanyService;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Diagnostics\Debugger;

/**
 * Class CompanyUserForm
 * @package AppForms
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class CompanyUserForm extends AppForms
{

    /** @var \Model\Service\CompanyService */
    private $companyService;
    private $passwordRequired;

    /**
     * @param Presenter $presenter
     * @param CompanyService $company
     */
    public function __construct(Presenter $presenter, CompanyService $company)
    {
        parent::__construct(get_class($this), $presenter);
        $this->companyService = $company;
    }

    /**
     * @param $name
     *
     * @return Form|\Nette\ComponentModel\IComponent
     */
    protected function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);

        $this->form->addGroup('Credentials');
        $this->form->addHidden('id');
        $this->form->addText('username', 'Username')
                ->setRequired();
        $this->form->addText('company_name', 'Company name')
                ->setRequired();
        $this->form->addText('email', 'Email')
                ->addRule(Form::EMAIL, 'You must type valid email address!')
                ->setRequired();
        $this->form->addPassword('password', 'Password');
        if ($this->passwordRequired) {
            $this->form['password']->setrequired();
        }
        $this->form->addSelect('view', 'View', \Model\Mapper\Dibi\CompanyDibiMapper::getViewOptions());
        
        $this->form->addSubmit('submit', 'Save user');
        $this->form->onSuccess[] = $this->saveUser;

        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function saveUser(Form $form)
    {
        
        $values = $form->values;
        if ($values->id) {
            $user = $this->companyService->findUser($values->id);
            $user->setValues($values);
        } else {
            $user = new \Model\Entity\Company\UserEntity($values);
        }
        try {
            $this->companyService->save($user);
            if ($values->password != '') {
                $this->companyService->updatePassword($user, $values->password);
            }
            $this->presenter->flashMessage('User was saved', 'success');
            $this->presenter->redirect('default');
        } catch (\DibiDriverException $e) {
            Debugger::log($e, Debugger::ERROR);
            $form->addError('There was an error with saving user. The error was reported!');
        }
    }

    public function entityToForm(UserEntity $user)
    {
        $data = array(
            'id' => $user->id,
            'username' => $user->username,
            'company_name' => $user->company_name,
            'email' => $user->email,
            'view' => $user->view,
        );
        $this->setDefaultValues($data);
    }

    /**
     * @param bool $bool
     */
    public function setPasswordRequired($bool = true)
    {
        $this->passwordRequired = $bool;
    }

}
