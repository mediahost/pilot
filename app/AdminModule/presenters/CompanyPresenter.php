<?php

namespace AdminModule;

use AppForms\CompanyUserForm;
use Model\Service\CompanyService;

/**
 * Class CompanyPresenter
 * @package AdminModule
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class CompanyPresenter extends BasePresenter
{

    /** @var  CompanyService */
    private $company;

    /**
     * @param CompanyService $service
     */
    public function injectCompany(CompanyService $service)
    {
        $this->company = $service;
    }

    public function startup()
    {
        parent::startup();
        $this->checkAccess("backend", "access");
    }

    public function actionAdd()
    {
        $this['editUser']->setPasswordRequired(true);
        $this['editUser']->entityToForm(new \Model\Entity\Company\UserEntity);
        $this->setView("edit");
    }

    /**
     * @param $id
     */
    public function actionEdit($id)
    {
        $user = $this->company->findUser($id);
        if (!$user) {
            $this->flashMessage("User with ID '$id' does NOT exist!", 'error');
            $this->redirect('default');
        }
        // For editing we don't need to set password
        $this['editUser']->setPasswordRequired(FALSE);
        // set default values
        $this['editUser']->entityToForm($user);
    }

    /**
     * @return CompanyUserForm
     */
    protected function createComponentEditUser()
    {
        return new CompanyUserForm($this, $this->company);
    }

    protected function createComponentCompanyUsersGrid()
    {
        $dataSource = $this->company->getDataGrid();
        $grid = new \CompanyUsersGrid($dataSource, $this, $this->translator, $this->company);

        return $grid;
    }

}
