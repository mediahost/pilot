<?php

namespace Model\Service;

use Nette\Security,
    Nette\Utils\Strings,
    Nette\Http\Session,
    Nette\Application\UI\Presenter,
    Model\Mapper\Dibi\UserDibiMapper,
    Model\Mapper\Dibi\AuthDibiMapper,
    Model\Service\MailService,
    Model\Entity\AuthEntity,
    Model\Entity\SignInEntity,
    Model\Entity\UserEntity,
    Model\Entity\AdapterUserEntity;

/**
 * Authenticator Service
 *
 * @author Petr Poupě
 */
class UserService implements Security\IAuthenticator
{

    /** @var UserDibiMapper */
    private $mapper;

    /** @var AuthDibiMapper */
    private $authMapper;

    /** @var MailService */
    private $mail;

    /** @var Session */
    private $session;

    /** @var array */
    private $KCFinderDefaults;
    
    /** @var UserDocService */
    protected $userDocService;

    public function __construct(UserDibiMapper $mapper, AuthDibiMapper $authMapper, MailService $mails, Session $session, $KCFinderParams, \Model\Service\UserDocService $userDocService)
    {
        $this->mapper = $mapper;
        $this->authMapper = $authMapper;
        $this->mail = $mails;
        $this->session = $session;
        $this->KCFinderDefaults = $KCFinderParams;
        $this->userDocService = $userDocService;
    }

// <editor-fold defaultstate="collapsed" desc="sign in">

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($id) = $credentials;

        /* @var $user UserEntity */
        $user = $this->mapper->find($id);

        if ($user->id === NULL) {
            throw new Security\AuthenticationException("This user isn't exists.", self::IDENTITY_NOT_FOUND);
        }

        if (!$user->active) {
            throw new Security\AuthenticationException("This account isn't active.", self::FAILURE);
        }

        $this->setLastSign($user);

        return new Security\Identity($user->id, $user->role, $user->toArray());
    }

    /**
     * Sign user from inserted entity
     * @param type $user convertable in AuthEntity
     * @param \Nette\Security\User $userContext
     * @throws \Nette\Security\AuthenticationException
     */
    public function sign(UserEntity $user, Security\User $userContext, $remember = TRUE)
    {
        $userContext->setAuthenticator($this);

        $longExpiration = "+ 14 days";
        $shortExpiration = "+ 20 minutes";
        if ($remember) {
            $userContext->setExpiration($longExpiration, FALSE, TRUE);
        } else {
            $userContext->setExpiration($shortExpiration, TRUE, TRUE);
        }
        $userContext->login($user->id);

        if ($userContext->isAllowed('KCFinder', 'view')) {
            $section = $this->session->getSection("KCFINDER");
            if ($remember) {
                $section->setExpiration($longExpiration, FALSE);
            } else {
                $section->setExpiration($shortExpiration, TRUE);
            }
            if ($userContext->isAllowed('KCFinder', 'edit')) {
                $this->KCFinderDefaults['access'] = array(
                    'files' => array(
                        'upload' => TRUE,
                        'delete' => TRUE,
                        'copy' => TRUE,
                        'move' => TRUE,
                        'rename' => TRUE,
                    ),
                    'dirs' => array(
                        'create' => TRUE,
                        'delete' => TRUE,
                        'rename' => TRUE,
                ));
            }
            $section->CONFIG = $this->KCFinderDefaults;
        }

        return $user;
    }

    public function checkVerification(AuthEntity $auth, Presenter $presenter)
    {
        if ($auth->id !== NULL) {
            if ($auth->verified === FALSE) {
                if ($auth->verifyCode === NULL) {
                    $auth->verifyCode = \Shopbox\Helpers::generateCode(FALSE, 20);
                    $this->saveAuth($auth);
                }
                $userReaload = $this->findByAuth($auth);

                $mail = $this->mail->create($presenter->lang);
                $mail->setTo($userReaload->mail);
                $mail->selectFrom(MailFactory::FROM_NOREPLY);
                $mail->selectMail(MailFactory::MAIL_SIGN_VERIFY, array(
                    "link" => $presenter->link("//Sign:verify", array(
                        "id" => $userReaload->id,
                        "code" => $auth->verifyCode,
                        "backlink" => NULL)),
                    "code" => $auth->verifyCode,
                ));
                $mail->send();


                throw new \Nette\Security\AuthenticationException(
                "This account is waiting for verification.", \Nette\Security\IAuthenticator::NOT_APPROVED);
            }
        }
    }

    /**
     * Find App authorization and check inserted password
     * @param \Model\Entity\SignInEntity $entity
     * @return AuthEntity
     * @throws Security\AuthenticationException
     */
    public function checkAppAuth(SignInEntity $entity)
    {
        $auth = $this->findAuth($entity->login);
        if ($auth->id !== NULL) {
            if (self::checkPassword($auth->password, $entity->password, $auth->salt)) {
                return $auth;
            } else {
                throw new Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
            }
        } else {
            throw new Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        }
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="sign out">
    /**
     * Logout all types of user
     * @param \Nette\Security\User $userContext
     * @param type $redirectUri
     * @param \Illagrenan\Facebook\FacebookConnect $fb
     */
    public function logout(Security\User $userContext, $redirectUri, \Illagrenan\Facebook\FacebookConnect $fb)
    {
        $this->session->getSection("KCFINDER")->remove();
        $this->session->getSection("smartJobFilter")->remove();

        if ($fb->isLoggedIn() !== FALSE) { // FB logout
            $fb->setRedirectUri($fb->getLogoutUrl(array('next' => $redirectUri)));
            $fb->destroySession();
            $fb->logout(TRUE);
        }

        $userContext->logout(TRUE);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="data source">
    public function getDataGrid()
    {
        return $this->mapper->allDataSource();
    }

    public function getUserFromIdentity(Security\Identity $identity)
    {
        $user = $this->mapper->load($identity->data);
        return $user;
    }
    
    public function getUsers() 
    {
	$users = $this->mapper->findAll()->fetchPairs("id", "mail");
	return $users;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="find">
    /**
     * Get or create user by inserted data format
     * @param type $user
     * @return UserEntity
     */
    public function find($id)
    {
        return $this->mapper->find($id);
    }

    public function findByMail($mail)
    {
        return $this->mapper->findBy(array("mail" => $mail));
    }

    public function findByAuthMail($mail)
    {
        $findedauth = $this->authMapper->findByLogin($mail);
        return $this->mapper->find($findedauth->userId);
    }

    public function findByAuth(AuthEntity $auth)
    {
        $findedauth = $this->authMapper->find($auth->key, $auth->source);
        return $this->mapper->find($findedauth->userId);
    }
    
    /**
     * 
     * @param type $token
     * @return UserEntity
     */
    public function findByToken($token)
    {
        return $this->mapper->findOneBy(array('profile_token' => $token));
    }

    public function findAll()
    {
        return $this->mapper->findAll();
    }

    public function findAuth($key)
    {
        return $this->authMapper->findByLogin($key);
    }

    public function findAuthByUser($userId)
    {
        return $this->authMapper->findByUser($userId);
    }

    public function findAuthByAuth(AuthEntity $auth)
    {
        return $this->authMapper->findByLogin($auth->key, $auth->source);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="save & create">
    public function save(UserEntity $user)
    {
        return $this->mapper->save($user);
    }

    public function saveAuth(AuthEntity $entity)
    {
        return $this->authMapper->save($entity);
    }

    /**
     * Souhrn podmínek, které musí autorizace splňovat, aby mohl být vytvořen NOVÝ uživatel
     * - musí obsahovat mail, který je unikátní
     * - v případě APP musí mít unikátní login
     * @param type $source
     * @param type $mail
     * @param type $key
     * @return boolean
     */
    public function isCreatableAdapter(AdapterUserEntity $adapter)
    {
        $isCreatable = \Nette\Utils\Validators::isEmail($adapter->mail) && $this->isUniqueMail($adapter->mail);
        if ($adapter->source === AuthEntity::SOURCE_APP) {
            if ($adapter->id !== NULL) {
                $isCreatable &= $this->isUniqueLogin($adapter->id);
            } else {
                $isCreatable = FALSE;
            }
        }
        return $isCreatable;
    }

    /**
     * Podmínky pro možnost připojení k uživateli - spojuje se podle mailu
     * - musí mít existující email
     * @param \Model\Entity\AdapterUserEntity $adapter
     * @return boolean
     */
    public function isJoinableAdapter(AdapterUserEntity $adapter)
    {
        if (\Nette\Utils\Validators::isEmail($adapter->mail)) {
            $user = $this->findByMail($adapter->mail);
            return ($user->id !== NULL);
        }
        return FALSE;
    }

    public function createAppAccount(AdapterUserEntity $adapter, $password, $send = FALSE, $lang = NULL, Presenter $presenter = NULL)
    {
        $auth = new AuthEntity($adapter);
        $auth->salt = UserService::generateSalt();
        $auth->password = UserService::calculateHash($password, $auth->salt);

        $newUser = new UserEntity;
        if ($this->isCreatableAdapter($adapter)) {
            $newUser = $this->createFromAdapter($adapter, $auth);
        } else if ($this->isJoinableAdapter($adapter)) {
            $newUser = $this->joinFromAdapter($adapter, $auth);
        }

        if ($newUser->id !== NULL) {
            if ($send && $lang !== NULL && $presenter !== NULL) {
                $mail = $this->mail->create($lang);
                $mail->setTo($newUser->mail);
                $mail->selectFrom(MailFactory::FROM_NOREPLY);
                $mail->selectMail(MailFactory::MAIL_SIGN_CREATE_ACCOUNT, array(
                    'username' => $auth->key,
                    'password' => $password,
                    'code' => $auth->verifyCode,
                    'link' => $presenter->link("//Sign:verify", array(
                        "id" => $newUser->id,
                        "code" => $auth->verifyCode,
                        "backlink" => NULL)),
                ));
                $mail->send();
            }
        }
        return $newUser;
    }

    public function createFromAdapter(AdapterUserEntity $adapter, $auth = NULL)
    {
        $user = new UserEntity($adapter);
        $user->created = time();
        $userSaved = $this->save($user);

        if (!$auth instanceof AuthEntity) {
            $auth = new AuthEntity($adapter);
        }
        $auth->userId = $userSaved->id;
        $this->saveAuth($auth);

        return $userSaved;
    }

    public function joinFromAdapter(AdapterUserEntity $adapter, $auth = NULL)
    {
        $user = $this->findByMail($adapter->mail);
        if ($user->id !== NULL) {
            if (!$auth instanceof AuthEntity) {
                $auth = new AuthEntity($adapter);
            }
            $auth->userId = $user->id;
            $this->saveAuth($auth);

            if ($user->birthday === NULL) {
                $user->birthday = $adapter->birthday;
            }
            if ($user->lang === NULL) {
                $user->lang = $adapter->lang;
            }
            if ($user->username === NULL) {
                $user->username = $adapter->username;
            }
        }
        return $user;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="password">

    /**
     * Reset password for App authorization
     * @param type $login
     * @param type $send
     * @param type $lang
     * @return type
     */
    public function resetAppPassword($login, $send = FALSE, $lang = NULL)
    {
        $auth = $this->findAuth($login);
        return $this->changeAppPassword($auth, NULL, $send, $lang);
    }

    public function resetAppPasswordByMail($mail, $send = FALSE, $lang = NULL)
    {
        $user = $this->findByMail($mail);
        if ($user->id !== NULL) {
            $auth = $this->findAuthByUser($user->id);
            return $this->changeAppPassword($auth, NULL, $send, $lang);
        } else {
            return FALSE;
        }
    }

    /**
     * Changing password for App authorization
     * @param type $id
     * @param type $oldPass
     * @param type $newPass
     * @param type $send
     * @param type $lang
     * @return boolean
     */
    public function setNewAppPassword($id, $oldPass, $newPass, $send = FALSE, $lang = NULL)
    {
        $auth = $this->authMapper->findByUser($id);
        if ($auth->id !== NULL) {
            if ($oldPass === FALSE || self::checkPassword($auth->password, $oldPass, $auth->salt)) {
                return $this->changeAppPassword($auth, $newPass, $send, $lang);
            }
        }
        return FALSE;
    }

    /**
     * Applicate change password for App authorization
     * @param type $login
     * @param type $send
     * @param type $lang
     * @return boolean
     */
    private function changeAppPassword(AuthEntity $auth, $pass = NULL, $send = FALSE, $lang = NULL)
    {
        if ($auth->id !== NULL) {
            $newPassword = $pass === NULL ? \CommonHelpers::generatePassw(8) : $pass;
            $auth->password = UserService::calculateHash($newPassword, $auth->salt);
            $auth = $this->authMapper->save($auth, "password");

            if ($send && $lang !== NULL) {
                $mail = $this->mail->create($lang);
                $mail->setTo($auth->key);
                $mail->selectFrom(MailFactory::FROM_NOREPLY);
                $mail->selectMail(MailFactory::MAIL_SIGN_CHANGE_PASSWORD, array(
                    'username' => $auth->key,
                    'password' => $newPassword,
                ));
                $mail->send();
            }
            return TRUE;
        }
        return FALSE;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="edit">
    /**
     * Verify account
     * @param type $id
     * @param type $code
     * @return boolean
     */
    public function verify($id, $code)
    {
        $auth = $this->authMapper->findBy(array(
            "userId" => $id,
            "verifyCode" => $code,
        ));
        $user = $this->mapper->find($auth->userId);
        if ($auth->id !== NULL) {
            $auth->verified = TRUE;
            $auth->verifyCode = NULL;
            $this->authMapper->save($auth, array("verified", "verifyCode"));
        }
        return $user;
    }

    public function setActive($id, $active = TRUE)
    {
        $user = $this->find($id);
        if ($user->id !== NULL) {
            $user->active = $active;
            $this->mapper->save($user, "active");
        }
    }

    /**
     * Setting time of last sign for inserted user
     * @param \Model\Entity\UserEntity $user
     * @return \Model\Entity\UserEntity
     */
    private function setLastSign(UserEntity $user)
    {
        $user->lastSign = time();
        return $this->mapper->save($user);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="delete">

    public function delete(UserEntity $item)
    {
        foreach ($this->userDocService->findByUser($item->id) as $doc) {
            $this->userDocService->delete($doc);
        }
        return $this->mapper->delete($item);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="check uniques">

    public function isUniqueMail($mail, $userId = NULL)
    {
        return $this->mapper->isUniqueMail($mail, $userId);
    }

    public function isUniqueLogin($login, $userId = NULL)
    {
        return $this->authMapper->isUniqueKey($login, $userId);
    }
    
    public function isUniqeProfileToken($token, $userId = NULL)
    {
        return $this->mapper->isUniqeProfileToken($token, $userId);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="static functions">
    /**
     * Compare hash and password
     * @param type $hash
     * @param type $password
     * @param type $salt
     * @return boolean
     */
    public static function checkPassword($hash, $password, $salt)
    {
        return (bool) ($hash === self::calculateHash($password, $salt));
    }

    /**
     * Computes salted password hash.
     * @param  string
     * @return string
     */
    public static function calculateHash($password, $salt = NULL)
    {
        if ($password === Strings::upper($password)) { // perhaps caps lock is on
            $password = Strings::lower($password);
        }
        return crypt($password, $salt ? : self::generateSalt());
    }

    /**
     * Generate salt
     * @return string
     */
    public static function generateSalt()
    {
        return '$2a$07$' . Strings::random(22);
    }

// </editor-fold>
    
    public function generateProfileToken(UserEntity $userEntity, \Model\Entity\CvEntity $cv)
    {
        $fullName = $cv->getFullName(TRUE);
        $token = $fullName;
        $counter = 2;
        while (!$this->isUniqeProfileToken(Strings::webalize($token), $userEntity->id)) {
            $token = $fullName . '-' . $counter;
            $counter++;
        }
        return Strings::webalize($token);
    }
    
}
