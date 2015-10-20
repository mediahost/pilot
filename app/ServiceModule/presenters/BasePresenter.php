<?php

namespace ServiceModule;

/**
 * Service BasePresenter
 *
 * @author Petr Poupě
 */
abstract class BasePresenter extends \BasePresenter
{
    
    protected function checkAccess($resource = \Acl\Permission::ALL, $privilege = \Acl\Permission::ALL, $redirect = TRUE)
    {
        // TODO: implement acces by IP
        parent::checkAccess($resource, $privilege, $redirect);
    }

    public function startup()
    {
        $this->langs = $this->context->langs->getBackLanguages();
        
        parent::startup();
    }

}