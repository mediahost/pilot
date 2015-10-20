<?php

namespace Model\Service;

use Model\Entity\Company\UserEntity;
use Model\Mapper\Dibi\CompanyDibiMapper;
use Nette\Security\AuthenticationException;
use Nette\Object;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;

/**
 * Class CompanyService
 * @package Model\Service
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class CompanyService extends Object implements IAuthenticator
{

    /** @var \Model\Mapper\Dibi\CompanyDibiMapper */
    private $mapper;

    /**
     * @param CompanyDibiMapper $mapper
     */
    public function __construct(CompanyDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param UserEntity $user
     *
     * @return \DibiResult|int
     */
    public function save(UserEntity $user, $except = array())
    {
        return $this->mapper->save($user, $except);
    }

    /**
     * @param $id
     *
     * @return bool|UserEntity
     */
    public function findUser($id)
    {
        return $this->mapper->findUser($id);
    }

    /**
     * @return \DibiFluent
     */
    public function getDataGrid()
    {
        return $this->mapper->getDataGrid();
    }

    /**
     * @param array $credentials
     *
     * @return \Nette\Security\Identity
     * @throws \Nette\Security\AuthenticationException
     */
    function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $user = $this->mapper->getUserRow($username);
        if (!$user) {
            throw new AuthenticationException("User '$username' was not found!", self::IDENTITY_NOT_FOUND);
        } elseif (!UserService::checkPassword($user->password, $password, $user->salt)) {
            throw new AuthenticationException("Invalid password", self::INVALID_CREDENTIAL);
        }
        unset($user->password);
        unset($user->salt);
        return new Identity($user->id, [$user->role], $user->itemToData());
    }
    
    public function getPairs()
    {
        return $this->mapper->getPairs();
    }
    
    public function addCompanyPicture(\Nette\Http\FileUpload $file, UserEntity $company)
    {
        $id = $this->mapper->createCompanyPicture($company->id);
        \AppForms\AppForms::saveImg($file, 'companypicture', $id);
    }
    
    public function removeCompanyPicture($id, UserEntity $company)
    {
        $this->mapper->removeCompanyPicture($id);
        \AppForms\AppForms::removePhoto('companypicture', $id);
    }
    
    public function updatePassword(UserEntity $company, $password)
    {
        $salt = UserService::generateSalt();
        $hash = UserService::calculateHash($password, $salt);
        $this->mapper->updatePassword($company->id, $hash, $salt);
    }
    
    public function findUserBySlug($slug)
    {
        return $this->mapper->findUserBySlug($slug);
    }

}
