<?php

namespace ServiceModule;

/**
 * DefaultPresenter
 *
 * @author Petr Poupě
 */
class DefaultPresenter extends BasePresenter
{

    protected function beforeRender()
    {
        parent::beforeRender();
        $this->checkAccess("service", "access");
    }

}
