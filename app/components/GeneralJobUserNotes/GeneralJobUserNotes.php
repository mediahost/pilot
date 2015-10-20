<?php

namespace App\Components;

use Model\Service\JobService;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class GeneralJobUserNotes extends Control
{

	/** @var \DibiRow */
	protected $jobUser;

	/** @var JobService */
	protected $jobService;

	/**
	 * GeneralJobUserNotes constructor.
	 * @param JobService $jobService
	 */
	public function __construct(JobService $jobService)
	{
		$this->jobService = $jobService;
		parent::__construct();
	}

	public function setJobUser(\DibiRow $jobUser)
	{
		$this->jobUser = $jobUser;
		$this['form']->setDefaults($jobUser);
	}

	public function createComponentForm()
	{
	    $form = new Form();
		$form->addTextArea('note_general', 'General notes');
		$form->addTextArea('note_interview', 'Interview notes');
		$form->addTextArea('note_communication', 'Communication');
		$form->addTextArea('note_technical', 'Technical');
		$form->addTextArea('note_other', 'Other');
		$form->addSubmit('save', 'Save notes')
			->getControlPrototype()
			->addClass('button');
		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form)
	{
		$values = $form->values;
		$this->jobService->updateJobUserNotes($this->jobUser->id, $values);
		$this->redirect('this');
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/GeneralJobUserNotes.latte');
		$this->template->render();
	}

}