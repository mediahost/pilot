<?php

namespace AdminModule;

/**
 * FakePresenter
 *
 * @author Petr Poupě
 */
class FakePresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();
        $this->checkAccess("service", "access");
    }

    public function actionDefault()
    {
        $this['assignFakeUserForm']->setDefaults();
    }

    /**
     * Assign User to Fake Account factory.
     * @return Form
     */
    protected function createComponentAssignFakeUserForm()
    {
	return new \AppForms\AssignFakeAccountForm($this, $this->context->users);
    }

}
