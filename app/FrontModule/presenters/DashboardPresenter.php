<?php

namespace FrontModule;

/**
 * Dashboard Presenter
 *
 * @author Petr Poupě
 */
class DashboardPresenter extends BasePresenter
{

	private $jobs;
	private $where;

	/** @var \App\Model\Launchpad\LaunchpadApi */
	public $launchpad;

	/** @var \Model\Service\UserDocService */
	protected $userDocService;

	/** @var \Model\Entity\ChatEntity */
	protected $chat;

	/** @var \Model\Service\CandidateService */
	protected $candidateService;

	public function injectLaunchpad(\App\Model\Launchpad\LaunchpadApi $launchpad)
	{
		$this->launchpad = $launchpad;
	}

	public function injectUserDocService(\Model\Service\UserDocService $userDocService)
	{
		$this->userDocService = $userDocService;
	}

	public function injectCandidateService(\Model\Service\CandidateService $cs)
	{
		$this->candidateService = $cs;
	}

	public function startup()
	{
		parent::startup();
		$this->checkAccess("dashboard", "view");
	}

// <editor-fold defaultstate="collapsed" desc="view">

	public function setView($view, $action = NULL)
	{
		parent::setView($view === NULL ? "default" : $view);
		$this->template->action = $action === NULL ? $view : $action;
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="default">
	public function actionDefault($showGuide = FALSE)
	{
		$user = $this->userService->find($this->user->id);
		if (!$user->visitGuide) {
			$showGuide = TRUE;
			$user->visitGuide = TRUE;
			$this->context->users->save($user);
		}
		$cv = $this->cvService->getDefaultCv($this->user->id);
		$firstname = $user->firstName;
		$lastname = $user->lastName;
		if ($cv && $firstname == '') {
			$firstname = $cv->firstname;
		}
		if ($cv && $lastname == '') {
			$lastname = $cv->surname;
		}

		if ($firstname == '' || $lastname == '') {
			$step = 'fill identity';
		} else {
			if (!$user->launchpadVideoUrl) {
				$candidate = new \App\Model\Entity\Launchpad\Candidates\CandidateEntity(array(
					'custom_candidate_id' => $user->id,
					'email' => $user->mail,
					'first_name' => $firstname,
					'last_name' => $lastname,
				));
				$interviewId = 8327;
				$createdCandidate = $this->launchpad->setCandidate($candidate);
				$step = 'do interview';
				$link = $this->launchpad->getInviteLink($interviewId, $createdCandidate->getCandidateId())->getLink()['url'];
				$this->template->link = $link;
			} else {
				$step = 'review interview';
				$this->template->reviewLink = $user->launchpadVideoUrl;
			}
		}
		$this->template->step = $step;

		$this->setTimeline();

		$listCount = 6;
		$this->template->jobs = $this->context->jobs->getAll(NULL, $listCount, ['job_user.user_id' => $this->user->id], 'time');

		$this->template->cvs = $this->context->cv->findUsersCv($this->user->getId(), $listCount, TRUE);
		$this->template->listCount = $listCount;

		$this->template->recentActions = $this->context->actionlogs->getLast($this->user->getId(), $listCount);
		foreach ($this->template->recentActions as &$item) {
			$this->generateRecentLogLink($item);
		}

		$this->template->showWalkThrough = $showGuide;
	}

	public function renderDefault($action)
	{
		$this->template->docs = $this->userDocService->findByUser($this->user->id, 6);
		$jobFilter = $this->context->session->getSection('jobFilter');

		$this->setView(NULL, $action);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="recent actions">
	public function actionRecentActions($id = NULL)
	{
		$list = $this->context->actionlogs->getLast($this->user->getId());
		foreach ($list as &$item) {
			$this->generateRecentLogLink($item);
		}
		$this->setTimeline("recentActions");

		$this->template->list = $list;
		$this->template->activeId = $id;
	}

	public function renderRecentActions($action)
	{
		$this->setView(NULL, $action);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="job applies">
	public function actionJobApplies($id = NULL)
	{
		$list = $this->context->jobapplys->getLast($this->user->getId(), 50);
		foreach ($list as &$item) {
			$job = $this->context->profesia->findById($item->jobId);
			if ($job->id) {
				$item->jobExtId = $job->externalid;
			}
		}
		$this->setTimeline("jobApplies");

//        if ($id !== NULL) {
//            $this->template->item = $this->context->jobapplys->find($id);
//        } else {
		$this->template->list = $list;
		$this->template->activeId = $id;
//        }
	}

	public function renderJobApplies($action)
	{
		$this->setView(NULL, $action);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="jobs">

	public function actionJobs($last = NULL, $sort = "time")
	{
		$this->setFilter($last);
		$this->jobs = $this->context->jobs->getAllForCount(NULL, NULL, $this->where);
	}

	public function renderJobs($last = NULL, $sort = "time")
	{
		$columns = 2;
		$rows = 10;

		$vp = new \VisualPaginator($this, "job");
		$vp->setTranslator($this->translator);

		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = $columns * $rows;
		$paginator->itemCount = count($this->jobs);

		$this->jobs = $this->context->jobs->getAll($paginator->offset, $paginator->itemsPerPage, $this->where, $sort);

		$jobsCount = count($this->jobs);
		if (!$jobsCount) {
//            $this->flashMessage("No offers for this filter.", "warning");
		}

		$this->template->itemCountWithoutFilter = count($this->context->jobs->getAllForCount(NULL, NULL, ["job_user.user_id" => $this->user->id]));
		$this->template->jobs = $this->jobs;
		$this->template->jobsCount = $jobsCount;
		$this->template->columns = $columns;
		$this->template->cv = $this->context->cv->getDefaultCv($this->user->getId());

		$this->setView(NULL, $this->getParameter('action'));
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="video profile">

	public function renderVideoProfile()
	{
		$this->setView(NULL, $this->getParameter('action'));

		$user = $this->userService->find($this->user->id);
		$cv = $this->cvService->getDefaultCv($this->user->id);
		$firstname = $user->firstName;
		$lastname = $user->lastName;
		if ($cv && $firstname == '') {
			$firstname = $cv->firstname;
		}
		if ($cv && $lastname == '') {
			$lastname = $cv->surname;
		}

		if ($firstname == '' || $lastname == '') {
			$step = 'fill identity';
		} else {
			$candidate = new \App\Model\Entity\Launchpad\Candidates\CandidateEntity(array(
				'custom_candidate_id' => $user->id,
				'email' => $user->mail,
				'first_name' => $firstname,
				'last_name' => $lastname,
			));
			$interviewId = 8327;
			$createdCandidate = $this->launchpad->setCandidate($candidate);
			$cssUrl = $this->template->baseUri . '/css/launchpad-review-style.css';
			$reviewLink = $this->launchpad->getReviewInterviewLink($interviewId, $createdCandidate->getCandidateId(), $cssUrl)->getLink()['url'];
			$reviewLink = preg_replace('/^http:/', 'https:', $reviewLink);
			if ($reviewLink == "") {
				$step = 'do interview';
				$link = $this->launchpad->getInviteLink($interviewId, $createdCandidate->getCandidateId())->getLink()['url'];
				$this->template->link = $link;
			} else {
				if ($reviewLink != $user->launchpadVideoUrl) {
					$user->launchpadVideoUrl = $reviewLink;
					$this->userService->save($user);
				}
				$step = 'review interview';
				$this->template->reviewLink = $reviewLink;
			}
		}
		$this->template->step = $step;
	}

	public function createComponentFillIdentityForm()
	{

		$user = $this->userService->find($this->user->id);

		$form = new \Nette\Application\UI\Form;
		$form->getElementPrototype()->class = 'styled';
		$form->addText('first_name', 'First name')
				->setDefaultValue($user->firstName)
				->setRequired();
		$form->addText('last_name', 'Last name')
				->setDefaultValue($user->lastName)
				->setRequired();

		$form->addSubmit('save', 'Save');

		$form->onSuccess[] = $this->processFillIdentityForm;

		return $form;
	}

	public function processFillIdentityForm(\Nette\Application\UI\Form $form)
	{
		$user = $this->userService->find($this->user->id);
		$values = $form->values;
		$user->setFirstName($values->first_name);
		$user->setLastName($values->last_name);
		$this->userService->save($user);
		$this->redirect('this');
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="profile">

	public function renderProfile()
	{
		$candidates = $this->candidateService->getCandidates(array(
			'id' => $this->user->id
		));
		if (count($candidates) == 0) {
			$this->redirect('completingFullProfile');
		}
		$candidateEntity = $candidates[$this->user->id];
		$candidateEntity->cv = $this->cvService->getCv($candidateEntity->cvId);
		if (!$candidateEntity->cv->isCompleted()) {
			$this->redirect('completingFullProfile');
		}

		$this->template->candidate = $candidateEntity;
		$this->extendTemplate();
		$this->setView(NULL, $this->getParameter('action'));

		$templateName = $candidateEntity->cv->templateName;
		if ($templateName === NULL) {
			$templateName = "default";
		}
		$this->template->cvTemplateName = $templateName;
	}

	public function renderEditProfile()
	{
		$this->template->editProfileLink = 1;
	}

	public function renderEditProfileAddress()
	{
		$this->template->editProfileLink = 2;
	}

	public function renderEditProfileLinks()
	{
		$this->template->editProfileLink = 3;
	}

	public function renderEditProfilePhoto()
	{
		$this->template->editProfileLink = 4;
	}

	public function renderEditProfileSettings()
	{
		$this->template->editProfileLink = 5;
		$this->template->userEntity = $this->userService->find($this->user->id);
	}

	public function actionEditProfilePreferences()
	{
		$this['preferences']->onSuccess[] = function () {
			$this->flashMessage('Thank you! Information has been saved!', 'success');
			$this->redirect('this');
		};
	}

	public function renderEditProfilePreferences()
	{
		$this->template->editProfileLink = 6;
	}

	public function handleShareProfile()
	{
		$user = $this->userService->find($this->user->id);
		$user->is_profile_public = TRUE;
		if (!$user->profile_token) {
			$user->profile_token = $this->userService->generateProfileToken($user, $this->cvService->getDefaultCv($user->id));
		}
		$this->userService->save($user);
		$this->redirect('this');
	}

	public function handleUnShareProfile()
	{
		$user = $this->userService->find($this->user->id);
		$user->is_profile_public = FALSE;
		$this->userService->save($user);
		$this->redirect('this');
	}

	public function actionRequiredInfo()
	{
		$this['popup']->onSave[] = function (\Model\Entity\CvEntity $cv) {
			$user =  $this->userService->find($cv->userId);
			if ($user->isFinished()) {
				$this->flashMessage('Requested information was successfully completed', 'success');
				$this->redirect('Dashboard:');
			} else {
				$this->flashMessage('Information was successfully saved', 'success');
				$this->redirect('Dashboard:requiredUserInfo');
			}
		};
	}

	public function renderRequiredInfo()
	{
		$this->setView(NULL, $this->getParameter('action'));
		$this->template->cv = $this->context->cv->getDefaultCv($this->user->id);
	}

	public function actionRequiredUserInfo()
	{
		$this['preferences']->onSuccess[] = function (\Model\Entity\UserEntity $user) {
			$this->flashMessage('Requested information was successfully completed', 'success');
			$this->redirect('Dashboard:');
		};
	}

	public function renderRequiredUserInfo()
	{
		$this->setView(NULL, $this->getParameter('action'));
		$this->template->cv = $this->context->cv->getDefaultCv($this->user->id);
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="completing full profile">

	public function renderCompletingFullProfile()
	{
		if ($this->defaultCv && $this->defaultCv->isCompleted()) {
			$this->redirect('profile');
		}
		$this->template->cv = $this->defaultCv;
		$this->setView(NULL, $this->getParameter('action'));
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="messages">

	public function actionMessages()
	{
		if (!$this->defaultCv || !$this->defaultCv->isCompleted()) {
			$this->flashMessage('you haven\'t completed profile');
			$this->redirect('completingFullProfile');
		}
	}

	public function renderMessages()
	{
		$this->template->chats = $this->chatService->findChatsByUser($this->user->id);
		$this->setView(NULL, $this->getParameter('action'));
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="chat">

	public function actionChat($id)
	{
		$this->chat = $this->chatService->getChatById($id);
		if (!$this->chat || $this->chat->userId != $this->user->id) {
			$this->error();
		}

		$this->chatService->readChatByUser($this->chat);

		if (!$this->chat->is_completed) {
			$this->flashMessage('you haven\'t completed profile');
			$this->redirect('completingFullProfile');
		}
	}

	public function renderChat($id)
	{
		$this->template->chat = $this->chat;
		$this->template->messages = $this->chatService->findMessages($id);

		$this->setView(NULL, $this->getParameter('action'));
	}

	public function renderSettings()
	{
		$this->template->userEntity = $this->userService->find($this->user->id);
		$this->setView(NULL, $this->getParameter('action'));
	}

	public function handleSwitchNotifications()
	{
		$user = $this->userService->find($this->user->id);
		$user->chat_notifications = !$user->chat_notifications;
		$this->userService->save($user);
		$this->redirect('this');
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="docs">

	public function renderDocs()
	{
		$this->setView(NULL, $this->getParameter('action'));
	}

	public function handleDeleteDoc($docId)
	{
		$userDoc = $this->userDocService->get($docId);
		if ($userDoc->userId != $this->user->id) {
			$this->error();
		}
		$this->userDocService->delete($userDoc);
		$this->flashMessage("Document was succesfully deleted.", "success");
		$this->redirect('this');
	}

	public function handleDeleteAllDocs()
	{
		$userDocs = $this->userDocService->findByUser($this->user->id);
		foreach ($userDocs as $userDoc) {
			$this->userDocService->delete($userDoc);
		}
		$this->flashMessage("All documents were succesfully deleted.", "success");
		$this->redirect('this');
	}

	public function handleSwitchDocVisibility($docId)
	{
		$userDoc = $this->userDocService->get($docId);
		if ($userDoc->userId != $this->user->id) {
			$this->error();
		}
		$userDoc->public = $userDoc->public ? FALSE : TRUE;
		$this->userDocService->save($userDoc);
		$this->redirect('this');
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="private">

	private function setTimeline($type = NULL)
	{
		$revised = array();
		$this->template->events = $revised;
		$this->template->timelineType = $type;
	}

	private function generateRecentLogLink(\Model\Entity\ActionLogEntity &$item)
	{
		switch ($item->action) {
			case \Model\Entity\ActionLogEntity::JOB_APPLY:
				$item->link = $this->link("Dashboard:jobApplies", $item->attrs[0]);
				break;
			case \Model\Entity\ActionLogEntity::NEW_CV:
			case \Model\Entity\ActionLogEntity::SAVE_CV:
				$item->link = $this->link("Cv:", $item->attrs[0]);
				$cv = $this->context->cv->getCv($item->attrs[0], $this->user->getId());
				$item->attrs[0] = $cv;
				break;
			case \Model\Entity\ActionLogEntity::DELETE_CV:
				$item->link = NULL;
				break;
			case \Model\Entity\ActionLogEntity::FORUM_POST:
				$item->link = $this->link("Forum:post", $item->attrs[0]);
				$post = $this->context->forum->getPost($item->attrs[0]);
				$topic = $this->context->forum->getTopic($post->topicId);
				$item->attrs[0] = $topic;
				break;
			case \Model\Entity\ActionLogEntity::READ_BLOG:
				$item->link = $this->link("Blog:", $item->attrs[0]);
				$blog = $this->context->pages->getBlog($item->attrs[0], $this->lang);
				$item->attrs[0] = $blog;
				break;
			case \Model\Entity\ActionLogEntity::READ_JOB:
				$item->link = $this->link("Job:", $item->attrs[0]);
				$job = $this->context->profesia->findById($item->attrs[0]);
				$item->attrs[0] = $job;
				break;
		}
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="filter">

	private function setFilter($last = NULL)
	{
		$where = array();

		$jobFilter = $this->context->session->getSection('smartJobFilter');

		if ($jobFilter->type === NULL) {
			$this->loadFilter($jobFilter, $this->user->getIdentity()->smart_filter_settings);
		}

		if ($jobFilter->isSalary) {
			$where['job.salary_from'] = $jobFilter->salaryMin;
			$where['job.salary_to'] = $jobFilter->salaryMax;
		}

		$locations = is_array($jobFilter->location) ? $jobFilter->location : NULL;
		if ($locations) {
			$where['job.locations'] = $locations;
		}

		if ($jobFilter->skills) {
			$where['job.skills'] = $jobFilter->skills;
		}

		if (!empty($jobFilter->text)) {
			$where['job.text'] = $jobFilter->text;
		}

		$oldestDate = NULL;
		switch ($last) {
			case 'day':
				$oldestDate = \Nette\DateTime::from('midnight');
				break;
			case 'week':
				$oldestDate = \Nette\DateTime::from('midnight -6 days');
				break;
			case 'month':
				$oldestDate = \Nette\DateTime::from('midnight -29 days');
				break;
		}
		if ($oldestDate) {
			$where['datecreated'] = $oldestDate;
		}
		$where['job_user.user_id'] = $this->user->id;

		$this->where = $where;
	}

	private function loadFilter(&$jobFilter, $user)
	{
		if ($user !== NULL) {
			foreach ($user as $key => $value) {
				$jobFilter->$key = $value;
			}
		}
	}

	public function handleResetFilter()
	{
		$jobFilter = $this->context->session->getSection('smartJobFilter');
		$jobFilter->type = \AppForms\JobFilterAdvancedForm::SMART_SEARCH;
		$jobFilter->text = NULL;

		$jobFilter->location = NULL;
		$jobFilter->available = NULL;
		$jobFilter->isSalary = FALSE;
		$jobFilter->salaryMin = NULL;
		$jobFilter->salaryMax = NULL;
		$jobFilter->skills = array();

		$user = $this->context->users->find($this->user->getId());
		if ($user->id !== NULL) {
			$user->smartFilterSettings = array();
			$this->context->users->save($user);
		}
		$this->redirect("this");
	}

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="components">

	protected function createComponentJobSearch()
	{
		return new \AppForms\SelectJobTagsForm($this, $this->context->profesia);
	}

	protected function createComponentFilter()
	{
		return new \AppForms\JobFilterForm($this, $this->context->jobs, $this->context->users, $this->context->session);
	}

	public function createComponentDocForm()
	{
		return new \AppForms\AddUserDocForm($this, $this->context->userDoc);
	}

	public function createComponentChatMessageForm()
	{
		$form = new \AppForms\ChatMessageForm($this, $this->context->chat, $this->context->mail, TRUE);
		$form->setUserId($this->user->id);
		$form->setCompanyId($this->chat->companyId);
		$form->setSender(\AppForms\ChatMessageForm::SENDER_USER);
		$form->setGlobalSettingDestination(':Front:Dashboard:settings');
		return $form;
	}

	public function createComponentSocialLinksForm()
	{
		$form = new \AppForms\SocialLinksForm($this, $this->context->users);
		$form->setUserEntity($this->context->users->find($this->user->id));
		return $form;
	}

	public function createComponentProfileTokenForm()
	{
		$form = new \AppForms\ProfileTokenForm($this, $this->context->users);
		return $form;
	}

	protected function createComponentPersonalDetails()
	{
		$form = new \AppForms\Step1PersonalForm($this, $this->context->cv, $this->context->cv->getDefaultCv($this->user->getId()), FALSE);

		return $form;
	}

	protected function createComponentAddress()
	{
		$form = new \AppForms\Step1AddressForm($this, $this->context->cv, $this->context->cv->getDefaultCv($this->user->getId()), FALSE);

		return $form;
	}

	protected function createComponentPreferences()
	{
		$form = new \AppForms\PreferencesForm($this, $this->context->users, $this->context->skills, $this->context->getByType('Model\Service\AircraftService'));

		return $form;
	}

	protected function createComponentPhoto()
	{
		$form = new \AppForms\Step12Form($this, $this->context->cv, $this->context->cv->getDefaultCv($this->user->getId()), FALSE);

		return $form;
	}

	protected function createComponentPopup()
	{
		$form = new \AppForms\Step1Form($this, $this->context->cv, $this->context->cv->getDefaultCv($this->user->getId()), FALSE);

		return $form;
	}

// </editor-fold>
}
