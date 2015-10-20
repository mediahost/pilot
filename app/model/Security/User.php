<?php

namespace Model\Security;

class User extends \Nette\Security\User
{

    public function isCompany()
    {
        $storage = $this->getStorage();
        /* @var $storage \Nette\Http\UserStorage */

        $namespace = $storage->getNamespace();
        $storage->setNamespace('Company');
        $return = $this->isInRole('company');
        $storage->setNamespace($namespace);

        return $return;
    }

    public function getCompanyIdentity()
    {
        $storage = $this->getStorage();
        /* @var $storage \Nette\Http\UserStorage */

        $namespace = $storage->getNamespace();
        $storage->setNamespace('Company');
        $return = $this->getIdentity();
        $storage->setNamespace($namespace);

        return $return;
    }

    public function companyAllowedToCv(\Model\Entity\CvEntity $cv)
    {
        return TRUE;
    }
    
    public function setCompanyNamespace()
    {
        $this->getStorage()->setNamespace('Company');
    }
    
    public function setCandidateNamespace()
    {
        $this->getStorage()->setNamespace('');
    }

}
