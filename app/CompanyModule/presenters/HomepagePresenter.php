<?php

namespace CompanyModule;

use App\Components\GeneralJobUserNotes;

/**
 * Class HomepagePresenter
 * @package CompanyModule
 *
 * @author Marek Šneberger <marek@sneberger.cz>
 * @author Petr Poupě
 */
class HomepagePresenter extends BasePresenter
{

    private $itemsPerPage = 10;

    /** @var \Model\Service\CompanyService */
    protected $companyService;

    /** @var \Model\Entity\ChatEntity */
    protected $chat;

    /** @var \App\Components\JobUserNotesFactory */
    protected $jobUserNotesFactory;

    /** @var \DibiRow */
    protected $jobUser;

    public function injectJobUserNotesFactory(\App\Components\JobUserNotesFactory $factory)
    {
        $this->jobUserNotesFactory = $factory;
    }

    public function injectCompanyService(\Model\Service\CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    protected function afterRender()
    {
        $this->template->action = $this->getParameter('action');
        $this->setView('default');
        parent::afterRender();
    }

	public function actionDefault($itTests = NULL)
	{
		if ($itTests) {
			$this->flashMessage('You need to upgrade your license to set IT tests', 'info');
		}
	}

    public function renderDefault()
    {
        $this->redirect('jobs');

        $candidatesFilter = $this->session->getSection('candidatesFilter');
        $where = array(
            "text" => $candidatesFilter->text,
            "skills" => $candidatesFilter->skills,
            "registered_until" => \Model\Mapper\Dibi\CompanyDibiMapper::getRegistrationDate($this->user->identity->view),
        );

        $candidatesCount = $this->candidates->getCandidatesCount($where);

        $paginator = $this->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPage;
        $paginator->itemCount = $candidatesCount;

        $candidates = $this->candidates->getCandidates($where, $paginator->offset, $paginator->itemsPerPage);
        $this->extendCandidates($candidates);

        $this->template->candidates = $candidates;
        $this->extendTemplate();
    }

	public function actionFavorites($itTests = NULL)
	{
		if ($itTests) {
			$this->flashMessage('You need to upgrade your license to set IT tests', 'info');
		}
	}

    public function renderFavorites()
    {
        $userId = $this->user->getId();
        $favoritesCount = $this->candidates->getFavoritesCount($userId);

        $paginator = $this->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPage;
        $paginator->itemCount = $favoritesCount;

        $favorites = $this->candidates->getFavorites($userId, $paginator->offset, $paginator->itemsPerPage);
        $this->extendCandidates($favorites);

        $this->template->favorites = $favorites;
        $this->extendTemplate();
    }

    public function renderMessages()
    {
        $this->template->chats = $this->chatService->findChatsByCompany($this->user->id);
    }

    public function renderSettings()
    {
        $this->template->userEntity = $this->companyService->findUser($this->user->id);
    }

    public function handleSwitchNotifications()
    {
        $user = $this->companyService->findUser($this->user->id);
        $user->chat_notifications = !$user->chat_notifications;
        $this->companyService->save($user, array('password', 'role', 'salt', 'username', 'id', 'email'));
        $this->redirect('this');
    }

    public function actionChat($id)
    {
        $this->chat = $this->chatService->getChatById($id);
        if (!$this->chat || $this->chat->companyId != $this->user->id) {
            $this->error();
        }
        if (!$this->chat->is_completed) {
            $this->flashMessage('candidate profile deleted');
            $this->redirect('messages');
        }
    }

    public function renderChat($id)
    {
        $this->chatService->readChatByCompany($this->chat);
        $this->template->chat = $this->chat;
        $this->template->messages = $this->chatService->findMessages($id);
    }

    public function actionNotes($id)
    {
        $this->jobUser = $jobUser = $this->context->jobs->getJobUser($id);
        if (!$jobUser) {
            $this->error();
        }
        $job = $this->context->jobs->find($jobUser->job_id);
        if ($job->company_id != $this->user->id) {
            $this->error();
        }
        $this->template->job = $job;
        $candidates = $this->candidates->getCandidates(array(
            'id' => $jobUser->user_id
        ));
        $candidate = $candidates[$jobUser->user_id];

        $this->template->candidateUser = $this->context->users->find($jobUser->user_id);
        $candidate->cv = $this->cvs->getCv($candidate->cvId);
        $this->template->candidate = $candidate;
        $this->extendTemplate();
    }

    public function handleFavorite($candidateId)
    {
        if ($this->candidates->setFavorite($this->user->getId(), $candidateId)) {
            $this->flashMessage("User was add as favorite", "success");
        } else {
            $this->flashMessage("User wasn't add as favorite", "warning");
        }
        $this->redirect("favorites");
    }

    public function handleUnfavorite($candidateId)
    {
        if ($this->candidates->unsetFavorite($this->user->getId(), $candidateId)) {
            $this->flashMessage("User was remove from favourites", "success");
        } else {
            $this->flashMessage("User wasn't remove from favourites", "warning");
        }
        $this->redirect("favorites");
    }

    protected function createComponentFilter()
    {
        return new \AppForms\CandidatesFilterForm($this, $this->company, $this->cvs, $this->context->session);
    }

    protected function getPaginator()
    {
        $vp = new \VisualPaginator($this, 'vp');
        return $vp->getPaginator();
    }

    private function extendCandidates(array &$candidates)
    {
        foreach ($candidates as &$candidate) {
            $candidate->cv = $this->cvs->getCv($candidate->cvId);
        }
    }

    protected function createComponentEditJobForm()
    {
        $form = new \AppForms\EditJobForm($this, $this->context->jobs, $this->context->location, $this->context->jobscategory, $this->context->getByType('\Model\Service\CompanyService'));
        $form->setCompanyId($this->user->id);
        $form->setOnSaveCallback($this->onJobSave);
        $form->setOnSaveAndBackCallback($this->onJobSaveAndBack);
        if ($id = $this->getParameter('id')) {
            $form->setId($id);
        }
        return $form;
    }

    public function onJobSave(\Model\Entity\JobEntity $job)
    {
        $this->redirect('job', $job->id);
    }

    public function onJobSaveAndBack(\Model\Entity\JobEntity $job)
    {
        $this->redirect('jobs');
    }

    public function actionJob($id)
    {
        if ($id) {
            $job = $this->context->jobs->find($id);
            if ($job->company_id != $this->user->id) {
                $this->error();
            }
            $this['editJobForm']->setDefaults($job);
        }
        $this->template->bootstrapPlugins = TRUE;
    }

    public function actionJobs()
    {
        $this->template->jobs = $this->context->jobs->findBy('company_id', $this->user->id);
    }

    public function jobViewLinkFactory($row)
    {
        return $this->link(":Front:Jobs:show", $row['code']);
    }

    public function jobEditLinkFactory($row)
    {
        return $this->link("job", $row['id']);
    }

    public function jobDeleteLinkFactory($row)
    {
        return $this->link("deleteJob!", $row['id']);
    }

    public function handleDeleteJob($id)
    {
        $this->deleteJob($id);
        $this->redirect('this');
    }

    public function deleteJob($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->deleteJob($item);
            }
        } else {
            $job = $this->context->jobs->find($id);
            if ($job->company_id == $this->user->id) {
                if ($this->context->jobs->delete($id)) {
                    $this->flashMessage($this->translator->translate("Job '%s' was succesfull deleted", $job->name), "success");
                } else {
                    $this->flashMessage($this->translator->translate("Job '%s' wasn't deleted", $job->name), "danger");
                }
            }
        }
    }

    public function createComponentCompanyProfileForm()
    {
        return new \App\CompanyProfileForm($this, $this->companyService, $this->user);
    }

    public function createComponentPicsForm()
    {
        return new \AppForms\AddCompanyPicsForm($this, $this->companyService, $this->user);
    }

    public function renderProfile()
    {
        $this->template->company = $this->companyService->findUser($this->user->id);
    }

    public function handleDeleteLogo()
    {
        \AppForms\AppForms::removePhoto('company', $this->user->id);
        $this->flashMessage("Logo has been removed", "success");
        $this->redirect('this');
    }

    public function handleDeleteCompanyPictures()
    {
        $company = $this->companyService->findUser($this->user->id);
        foreach ($company->getPictures() as $picture) {
            $this->companyService->removeCompanyPicture($picture, $company);
        }
        $this->flashMessage("All pictures has been removed", "success");
        $this->redirect('this');
    }

    public function handleDeleteCompanyPicture($id)
    {
        $company = $this->companyService->findUser($this->user->id);
        if (in_array($id, $company->getPictures())) {
            $this->companyService->removeCompanyPicture($id, $company);
        } else {
            $this->error();
        }
        $this->redirect('this');
    }

    public function actionMatched($id, $category)
    {
        $job = $this->context->jobs->find($id);
        if (!$job || $job->company_id != $this->user->id) {
            $this->error();
        }
    }

    public function renderMatched($id, $category)
    {
        $matchedCandidates = $this->candidates->getMatched($id, TRUE, $category);
        $this->extendCandidates($matchedCandidates);

        $this->template->category = $category;
        $this->template->matchedCandidates = $matchedCandidates;
        $this->template->showStatusSelect = TRUE;
        if (count($matchedCandidates)) {
            $this->template->jobUserInfo = $this->context->jobs->getUserJobInfoByJob($id, TRUE);
        }
        $this->template->job = $this->context->jobs->find($id);
        $this->extendTemplate();
    }

    public function actionJobUserStatus($id, $userid, $status)
    {
        $job = $this->context->jobs->find($id);
        if (!$job || $job->company_id != $this->user->id) {
            $this->error();
        }
        $this->context->jobs->setJobUserStatusByJobAndUser($id, $userid, $status);
        $this->sendPayload();
    }

    public function createComponentNotes()
    {
        return $this->jobUserNotesFactory->create($this->presenter->getParameter('id'));
    }

    public function createComponentGeneralNotes()
    {
        $control = new GeneralJobUserNotes($this->context->jobs);
        $control->setJobUser($this->jobUser);
        return $control;
    }

    public function handleShortlist($ujid)
    {
        $jobUser = $this->context->jobs->getJobUser($ujid);
        $job = $this->context->jobs->find($jobUser->job_id);
        if ($job && $job->company_id == $this->user->id && $jobUser->category != \Model\Mapper\Dibi\JobsDibiMapper::JOB_USER_CATEGORY_SHORTLISTED) {
            $this->context->jobs->setJobUserCategory($jobUser->id, \Model\Mapper\Dibi\JobsDibiMapper::JOB_USER_CATEGORY_SHORTLISTED);
            $this->flashMessage('Candidate has been shortlisted for job '.$job->name);
        }
        $this->redirect('this#can-'.$jobUser->user_id);
    }

    public function handleReject($ujid)
    {
        $jobUser = $this->context->jobs->getJobUser($ujid);
        $job = $this->context->jobs->find($jobUser->job_id);
        if ($job && $job->company_id == $this->user->id && $jobUser->category != \Model\Mapper\Dibi\JobsDibiMapper::JOB_USER_CATEGORY_REJECTED) {
            $this->context->jobs->setJobUserCategory($jobUser->id, \Model\Mapper\Dibi\JobsDibiMapper::JOB_USER_CATEGORY_REJECTED);
            $cv = $this->context->cv->getDefaultCv($jobUser->user_id);
            $company = $this->context->getByType('\Model\Service\CompanyService')->findUser($job->company_id);
            $mail = $this->context->mail->create($this->lang);
            $mail->selectMail(\Model\Service\MailFactory::MAIL_REJECTED, array(
                'to' => $cv->email,
                'candidate_name' => $cv->getFullName(),
                'job_name' => $job->name,
                'company_name' => $company->company_name,
            ));
            $mail->send();
            $this->flashMessage('Candidate has been rejected for job '.$job->name);
        }
        $this->redirect('this#can-'.$jobUser->user_id);
    }

}
