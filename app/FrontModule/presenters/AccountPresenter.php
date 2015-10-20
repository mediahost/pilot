<?php

namespace FrontModule;

/**
 * AccountPresenter - user settings
 *
 * @author Petr Poupě
 */
class AccountPresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();
        $this->checkAccess("account", "edit");
    }
    
    public function actionDefault()
    {
        $this->redirect("info");
    }

    public function actionInfo()
    {
        $user = $this->context->users->find($this->user->getIdentity()->id);
        $this["accountInfoForm"]->setDefaults($user);
    }

    public function actionChangePassword()
    {
    }

    public function actionDelete()
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect("Homepage:");
        }
    }

// <editor-fold defaultstate="collapsed" desc="factories">

    /**
     * Account info factory.
     * @return Form
     */
    protected function createComponentAccountInfoForm()
    {
        return new \AppForms\AccountInfoForm($this, $this->context->users, $this->context->tag);
    }
    
    /**
     * Change password form factory.
     * @return Form
     */
    protected function createComponentChangePasswordForm()
    {
        return new \AppForms\ChangePassForm($this, $this->context->users);
    }

    /**
     * Delete account form factory.
     * @return Form
     */
    protected function createComponentDeleteAccountForm()
    {
        return new \AppForms\DeleteAccountForm($this, $this->context->users);
    }

// </editor-fold>
}
