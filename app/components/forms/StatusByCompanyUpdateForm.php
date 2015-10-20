<?php

namespace AppForms;

use Model\Mapper\Dibi\JobsDibiMapper;
use Model\Service\JobService;
use Nette\Application\UI\Presenter;
use Nette\Forms\Form;

class StatusByCompanyUpdateForm extends AppForms
{

	/** @var JobService */
	protected $jobService;

	/** @var int */
	protected $jobId;

	/** @var int */
	protected $candidateId;

	/** @var \DibiRow */
	protected $jobUser;

	public function __construct(Presenter $presenter, JobService $jobService)
	{
		parent::__construct('statusbycompnyform', $presenter);
		$this->jobService = $jobService;
	}

	protected function getJobUser()
	{
		if (!$this->jobUser) {
			$this->jobUser = $this->jobService->getJobUserByJobAndUser($this->jobId, $this->candidateId);
		}
		return $this->jobUser;
	}

	public function setJobId($jobId)
	{
		$this->jobId = $jobId;
	}

	public function setCandidateId($candidateId)
	{
		$this->candidateId = $candidateId;
	}

	public function setJobUserId($id)
	{
		$this->jobUser = $this->jobService->getJobUser($id);
	}

	public function createComponent($name)
	{
		$jobUser = $this->getJobUser();

	    $this->form->addSelect('status_by_company', 'Status', JobService::getStatusByCompanyList())
			->setPrompt('Custom status name')
			->setDefaultValue($jobUser->status_by_company)
			->getControlPrototype()->addClass('statusByCompanySelect');
		$this->form->addText('status_by_company_text', 'Custom status name')
			->setDefaultValue($jobUser->status_by_company_text);

		$this->form->addSubmit('update', 'Update');
		$this->form->onSuccess[] = $this->processForm;
		return $this->form;
	}

	public function processForm()
	{
		$values = $this->form->values;
		$jobUser = $this->getJobUser();
		$this->jobService->updateJobUserStatusByCompany($jobUser->id, $values->status_by_company, $values->status_by_company_text);
		$text = $this->jobService->getStatusByCompanyName($values->status_by_company) ?: $values->status_by_company_text;
		$this->jobService->addJobUserAction($jobUser->id, JobsDibiMapper::JOB_USER_ACTION_STATUS, $text);
		$this->presenter->redirect('this');
	}

}