<?php

namespace AdminModule;

use Model\Entity\UserEntity,
    Model\Service\UserService,
    Model\Service\PageService;

/**
 * UsersPresenter
 *
 * @author Stephen Monaghan
 */
class UsersPresenter extends BasePresenter
{

    public function startup()
    {
	parent::startup();
	$this->checkAccess("backend", "access");
    }

    public function actionEditUser($id)
    {
	$user = $this->context->users->find($id);
	if ($user->id === NULL) {
	    $this->flashMessage("User wasn't found", "warning");
	    $this->redirect("default");
	}
	$this["editUserForm"]->setDefaults($user, FALSE);
        $this["editUserForm"]->setStyle(\AppForms\AppForms::STYLE_METRONIC);
    }

    public function actionEditPassword($login)
    {
	$auth = $this->context->users->findAuth($login);
	if ($auth->id === NULL) {
	    $this->flashMessage("User wasn't found", "warning");
	    $this->redirect("default");
	}
	$this["editPasswordForm"]->setDefaults($auth->key);
    }

    /**
     * Edit Password factory.
     * @return Form
     */
    protected function createComponentEditUserForm()
    {
	return new \AppForms\AccountInfoForm($this, $this->context->users, $this->context->tag);
    }

    /**
     * Edit Password factory.
     * @return Form
     */
    protected function createComponentEditPasswordForm()
    {
	return new \AppForms\EditPasswordForm($this, $this->context->users, $this->context->mail);
    }

    protected function createComponentUsersGrid()
    {
	$dataSource = $this->context->users->getDataGrid();
	$grid = new \UsersGrid($dataSource, $this, $this->translator, $this->context->users, $this->context->tag);
	return $grid;
    }
	
	public function handleDelete($id)
	{
		$user = $this->context->users->find($id);
        $this->context->users->delete($user);
        $this->redirect('this');
	}
    
    public function actionFillUrl()
    {
        $userService = $this->context->users;
        /* @var $userService UserService */
        
        $cvService = $this->context->cv;
        /* @var $cvService \Model\Service\CvService */
        
        $launchpad = $this->context->launchpad;
        /* @var $launchpad \App\Model\Launchpad\LaunchpadApi */
        
        $users = $userService->findAll();
        
        
        foreach ($users as $user) {
            $user = $userService->find($user->id);
            
            $cv = $cvService->getDefaultCv($user->id);
            $firstname = $user->firstName;
            $lastname = $user->lastName;
            if ($cv && $firstname == '') {
                $firstname = $cv->firstname;
            }
            if ($cv && $lastname == '') {
                $lastname = $cv->surname;
            }

            if ($firstname == '' || $lastname == '') {
                continue;
            } else {
                $candidate = new \App\Model\Entity\Launchpad\Candidates\CandidateEntity(array(
                    'custom_candidate_id' => $user->id,
                    'email' => $user->mail,
                    'first_name' => $firstname,
                    'last_name' => $lastname,
                ));
                $interviewId = 5915;
                $createdCandidate = $launchpad->setCandidate($candidate);
                $cssUrl = $this->template->baseUri . '/css/launchpad-review-style.css';
                $reviewLink = $launchpad->getReviewInterviewLink($interviewId, $createdCandidate->getCandidateId(), $cssUrl)->getLink()['url'];
                $reviewLink = preg_replace('/^http/', 'https', $reviewLink);
                if ($reviewLink == "") {
                    continue;
                } else {
                    if ($reviewLink != $user->launchpadVideoUrl) {
                        $user->launchpadVideoUrl = $reviewLink;
                        $userService->save($user);
                    }
                }
            }
        }
        echo 'ok';
        $this->terminate();
    }
    
    public function renderDefault()
    {
        $this->template->tags = \Nette\Utils\Json::encode(array_values($this->context->tag->findAll()));
    }

}
