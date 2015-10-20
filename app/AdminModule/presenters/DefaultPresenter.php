<?php

namespace AdminModule;

/**
 * DefaultPresenter
 *
 * @author Petr Poupě
 */
class DefaultPresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();
        $this->checkAccess("backend", "access");
    }

}
