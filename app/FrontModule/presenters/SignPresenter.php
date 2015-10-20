<?php

namespace FrontModule;

use Model\Entity\AdapterUserEntity,
    Model\Entity\AuthEntity;

/**
 * Sign Presenter
 *
 * @author Petr Poupě
 */
class SignPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = NULL;

    /** @var string */
    private $redirectSucc = "Dashboard:";

    /** @var string */
    private $redirectIn = "Homepage:";

    private function _stopLoggedIn()
    {
        if ($this->user->isLoggedIn()) {
            $this->flashMessage('You are already logged in. Please signed out.', 'warning');
            $this->restoreRequest($this->backlink);
            $this->redirect($this->redirectIn);
        }
    }

    public function actionDefault()
    {
        $this->redirect("in");
    }

    public function actionIn()
    {
        $this->_stopLoggedIn();
    }
    
    public function actionCreate()
    {
        $this->_stopLoggedIn();
    }
    
    public function actionLostPass()
    {
        $this->_stopLoggedIn();
    }
    
    public function actionVerify($id, $code = NULL)
    {
        $this->_stopLoggedIn();
        
        if ($code !== NULL) {
            $user = $this->context->users->verify($id, $code);
            if ($user->id !== NULL) {
                try {
                    $this->context->users->sign($user, $this->user, FALSE);
                    $this->flashMessage("Your account was successfully verified", "success");
                    $this->restoreRequest($this->backlink);
                    $this->redirect($this->redirectSucc);
                } catch (\Nette\Security\AuthenticationException $e) {
                    $this->flashMessage($e->getMessage(), 'warning');
                    $this->restoreRequest($this->backlink);
                    $this->redirect($this->redirectIn);
                }
            } else {
                $this->flashMessage("This code is not valid for this ID", "warning");
                $this->redirect("this", array("id" => $id, "code" => NULL));
            }
        }
        
        $this->template->showForm = $code !== NULL;
        $this["verifyAccountForm"]->setDefaults($code);
    }

    public function actionInsertMail($mail = NULL)
    {
        $section = $this->session->getSection("waiting-adapter");
        $adapter = $section->adapter;
        if ($adapter instanceof AdapterUserEntity) {
            if ($mail !== NULL) {
                unset($section->adapter);
                $adapter->mail = $mail;
                $this->_sign($adapter);
                $this->restoreRequest($this->backlink);
                $this->redirect($this->redirectSucc);
            } else {
                $this["insertMailForm"]->setBacklink($this->backlink);
            }
        } else {
            $this->flashMessage("Signing token was expired. Pleas try sign again.", "warning");
            $this->redirect("in");
        }
    }

    public function actionOut()
    {
        $this->context->users->logout($this->user, $this->link("//this", array("backlink" => $this->backlink)), $this->context->facebook);
        $this->flashMessage('You have been signed out', 'info');
        $this->restoreRequest($this->backlink);
        $this->redirect($this->redirectIn);
    }

// <editor-fold defaultstate="collapsed" desc="social actions">
    public function actionFacebook()
    {
        $this->_stopLoggedIn();

        /* @var $fb Illagrenan\Facebook\FacebookConnect */
        $fb = $this->context->facebook;

        if ($fb->isLoggedIn() === FALSE) { // Autorizoval uživatel naši aplikaci?
            $redirectUri = $this->link("//this"); // URL návratu z fb
            \Nette\Diagnostics\Debugger::log($redirectUri);
            $fb->setRedirectUri($redirectUri);
            $fb->login(); // Přihlásíme ho přesměrováním na Login_URL
        } else { // Uživatel je přihlášený v aplikaci
            $this->_sign($fb->getFacebookUser());
            $fb->destroySession();
        }
        $this->restoreRequest($this->backlink);
        $this->redirect($this->redirectSucc);
    }

    public function actionTwitter()
    {
        $this->_stopLoggedIn();

        /* @var $twitter Netrium\Addons\Twitter\Authenticator */
        $twitter = $this->context->twitter->authenticator;

        try {
            try {
                $data = $twitter->tryAuthenticate();
            } catch (\OAuthException $e) {
                $this->flashMessage($e->getMessage(), 'error');
                $this->redirect($this->redirectIn);
            }

            if (array_key_exists('user', $data)) {
                $this->_sign($data['user']);
            } else {
                $this->flashMessage("Twitter authorization was failed. Please try again later.", 'warning');
                $this->redirect($this->redirectIn);
            }
        } catch (\Netrium\Addons\Twitter\AuthenticationException $e) {
            $this->flashMessage('Twitter authentication did not approve. Please try again later.', 'warning');
            $this->redirect($this->redirectIn);
        }

        $this->restoreRequest($this->backlink);
        $this->redirect($this->redirectSucc);
    }

    public function actionLinkedin()
    {
        $this->_stopLoggedIn();
        $this->flashMessage("Not implemented yet", 'warning');
        $this->redirect($this->redirectIn);
    }

    public function actionGoogle()
    {
        $this->_stopLoggedIn();

        $this->flashMessage("Not implemented yet", 'warning'); // po zaplacení klientem smazat
        $this->redirect($this->redirectIn); // po zaplacení klientem smazat

        $url = $this->context->google->getLoginUrl(array(
            'scope' => $this->context->params['google']['scope'],
            'redirect_uri' => $this->link('//googleAuth', array("backlink" => NULL)),
        ));

        //save backlink - google needs static URL
        $this->context->session->getSection("google-sign-api")->backlink = $this->backlink;

        $this->redirectUrl($url);
    }

    public function actionGoogleAuth($code = NULL, $error = NULL)
    {
        $section = $this->context->session->getSection("google-sign-api");
        $this->backlink = $section->backlink;
        unset($section->backlink);
        $this->_stopLoggedIn();

        if (!$error) {
            try {
                $token = $this->context->google->getToken($code, $this->link('//googleAuth', array("backlink" => NULL)));
                $this->_sign($this->context->google->getInfo($token));
            } catch (\Exception $e) {
                $this->flashMessage('Google authorization was expired. Try it again.', 'warning');
                $this->redirect($this->redirectIn);
            }
        } else {
            $this->flashMessage('Please allow this application access to your Google account in order to log in.', 'warning');
            $this->redirect($this->redirectIn);
        }

        $this->restoreRequest($this->backlink);
        $this->redirect($this->redirectSucc);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="sign">
    protected function _sign($unknownUser)
    {
        /* @var $userService \Model\Service\UserService */
        $userService = $this->context->users;
        try {
            if ($unknownUser instanceof AdapterUserEntity) {
                $signAdapter = $unknownUser;
            } else {
                $signAdapter = new AdapterUserEntity($unknownUser);
            }

            $requestedAuth = new AuthEntity($signAdapter);
            $auth = $userService->findAuthByAuth($requestedAuth);

            $user = $userService->findByAuth($auth);

            $userToSign = $user;
            if ($user->id === NULL) { // neexistuje autorizace s uživatelem
                if ($userService->isCreatableAdapter($signAdapter)) { // lze vytvořit nového uživatele
                    $userToSign = $userService->createFromAdapter($signAdapter);
                } else if ($userService->isJoinableAdapter($signAdapter)) { // lze napojit ke stávajícímu uživateli
                    $userToSign = $userService->joinFromAdapter($signAdapter);
                } else { // chybí email
                    $section = $this->session->getSection("waiting-adapter");
                    $section->setExpiration("+ 20 minutes");
                    $section->adapter = $signAdapter;
                    $this->flashMessage("In this sign is no e-mail. Please insert your e-mail.", "warning");
                    $this->redirect("insertMail", array("backlink" => $this->backlink));
                }
            }

            $authReaload = $userService->findAuthByAuth($requestedAuth);
            $userService->checkVerification($authReaload, $this);

            $userService->sign($userToSign, $this->user, $signAdapter->remember);
            $this->flashMessage("You have been signed in", 'success');
        } catch (\Nette\Security\AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), 'warning');
            switch ($e->getCode()) {
                case \Nette\Security\IAuthenticator::NOT_APPROVED:
                    $this->redirect("verify");
                    break;
                default:
                    $this->redirect($this->redirectIn);
                    break;
            }
        }
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="factories">

    /**
     * Lost password form factory.
     * @return Form
     */
    protected function createComponentLostPassForm()
    {
        return new \AppForms\LostPassForm($this, $this->context->users);
    }

    /**
     * Verify Account form factory.
     * @return Form
     */
    protected function createComponentVerifyAccountForm()
    {
        return new \AppForms\VerifyAccountForm($this, $this->context->users);
    }

    /**
     * Insert registration mail form factory.
     * @return Form
     */
    protected function createComponentInsertMailForm()
    {
        return new \AppForms\InsertRegistrationMailForm($this, $this->context->users);
    }
    
    /**
     * Sign-in form factory.
     * @return Form
     */
    protected function createComponentSignInForm()
    {
        $form = new \AppForms\SignInForm($this, $this->context->users);
        $form->setBacklink($this->getParameter('backlink'));
        $form->setStyled();
        return $form;
    }

// </editor-fold>
}
