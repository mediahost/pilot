<?php

namespace FrontModule;

use Model\Entity\UserEntity;

/**
 * Front BasePresenter
 *
 * @author Petr Poupě
 */
abstract class BasePresenter extends \BasePresenter
{

    /** @var \Model\Service\ChatService */
    protected $chatService;

    /** @var \Model\Entity\CvEntity */
    protected $defaultCv;

    /** @var \Model\Service\CvService */
    protected $cvService;

    /** @var \Model\Service\UserService */
    protected $userService;

	/** @var UserEntity */
	protected $userEntity;

    public function injectChatService(\Model\Service\ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function injectCvService(\Model\Service\CvService $cvservice)
    {
        $this->cvService = $cvservice;
    }

    public function startup()
    {
        $this->userService = $this->context->users;
        $this->langs = $this->context->langs->getFrontLanguages();

        parent::startup();

		if ($this->user->isLoggedIn() && ($this->presenter->name != 'Front:Sign' && $this->presenter->action != 'out')) {
			$this->defaultCv = $this->cvService->getDefaultCv($this->user->id);
			if ($this->defaultCv && !$this->defaultCv->isFinished()
					&& $this->presenter->name . ':' . $this->presenter->action != 'Front:Dashboard:requiredInfo'
					&& $this->presenter->name != 'Front:Sign') {
				$this->flashMessage('Your account isn\'t completed.', 'success');
				$this->redirect('Dashboard:requiredInfo');
			}
			$this->userEntity = $user = $this->userService->find($this->user->id);
			if ($user->id && !$user->isFinished()
					&& $this->presenter->name . ':' . $this->presenter->action != 'Front:Dashboard:requiredInfo'
					&& $this->presenter->name . ':' . $this->presenter->action != 'Front:Dashboard:requiredUserInfo'
					&& $this->presenter->name != 'Front:Sign') {
				$this->flashMessage('Your account isn\'t completed.', 'success');
				$this->redirect('Dashboard:requiredUserInfo');
			}
			if ($user->id && !$user->profile_token) {
				$user->is_profile_public = TRUE;
				$user->profile_token = $this->userService->generateProfileToken($user, $this->defaultCv);
				$this->userService->save($user);
			}
		}
    }

    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->topMenuItems = $this->context->pages->getPagesOnTop($this->lang);
        $this->template->sitemapTree = $this->context->pages->getSitemapTree($this->lang);
        $this->template->unreadMessagesCount = $this->chatService->getUnreadCountByUser($this->user->id);
        $this->template->lastChats = $this->chatService->findChatsByUser($this->user->id, 5);
        $this->template->defaultCv = $this->defaultCv;
		$this->template->userEntity = $this->userEntity;

        $this->template->registerHelper("br2nl", "\CommonHelpers::br2nl");
        $this->template->registerHelper("texy", "\MyTexy::helperTexy");
        $this->template->registerHelper("first", "\CommonHelpers::helperFirst");
        $this->template->registerHelper("implodeMy", "\CommonHelpers::concatArray");
        $this->template->registerHelper("dateLang", "\CommonHelpers::helperDateFormat");
        $this->template->registerHelper("currency", "\CommonHelpers::currency");
        $this->template->registerHelper("timeAgoInWords", "\CommonHelpers::timeAgoInWords");
		
//       $password = "FbKaqoBC";
//       $salt = \Model\Service\UserService::generateSalt();
//       $hash = \Model\Service\UserService::calculateHash($password, $salt);
//       \Nette\Diagnostics\Debugger::barDump($password, "open pass");
//       \Nette\Diagnostics\Debugger::barDump($hash, "hash pass");
//       \Nette\Diagnostics\Debugger::barDump($salt, "salt");
    }

    /**
     * Create account form factory.
     * @return Form
     */
    protected function createComponentCreateAccountForm()
    {
        return new \AppForms\CreateAccountForm($this, $this->context->users, FALSE);
    }

}