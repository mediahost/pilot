<?php

namespace App\Components;

class JobUserNotes extends \Nette\Application\UI\Control
{
    
    /** @var int */
    protected $jobUserId;
    
    /** @var \Model\Service\JobService */
    protected $jobService;
    
    /** @var \Nette\Localization\ITranslator */
    protected $translator;
    
    /** @var int */
    protected $adminId = NULL;
    
    /** @var int */
    protected $editNoteId = NULL;
    
    /** @var \DibiFluent */
    protected $notes;
    
    /** @var \Model\Service\CompanyService */
    protected $companyService;
    
    /** @var \Model\Service\UserService */
    protected $userService;
    
    public function __construct($id,
        \Model\Service\JobService $jobService,
        \Nette\Localization\ITranslator $translator,
        \Model\Service\CompanyService $companyService,
        \Model\Service\UserService $userService)
    {
        $this->jobUserId = $id;
        $this->jobService = $jobService;
        $this->translator = $translator;
        $this->companyService = $companyService;
        $this->userService = $userService;
        parent::__construct();
    }
    
    public function getNotes()
    {
        if (!isset($this->notes)) {
            $this->notes = $this->jobService->getNotesByJobUser($this->jobUserId);
        }
        return $this->notes;
    }
    
    public function setAdminId($id)
    {
        $this->adminId = $id;
    }
    
    public function render()
    {
        $admins = [];
        foreach ($this->getNotes() as $note) {
            if ($note->admin_id) {
                $admin = $this->userService->find($note->admin_id);
                $admins[$note->admin_id] = $admin->fullName;
            }
        }
        $jobUser = $this->jobService->getJobUser($this->jobUserId);
        $job = $this->jobService->find($jobUser->job_id);
        $this->template->setFile(__DIR__ . '/JobUserNotes.latte');
        $this->template->setTranslator($this->translator);
        $this->template->notes = $this->getNotes();
        $this->template->editNoteId = $this->editNoteId;
        $this->template->adminId = $this->adminId;
        $this->template->job = $job;
        $this->template->company = $this->companyService->findUser($job->company_id);
        $this->template->admins = $admins;
        $this->template->render();
    }
    
    public function createComponentAddForm()
    {
        $form = new \Nette\Application\UI\Form;
        $form->setTranslator($this->translator);
        
        $form->addTextArea('note')
            ->setRequired()
            ->getControlPrototype()
            ->addAttributes(['placeholder' => 'Write a note...']);
        $form->addSubmit('add', 'Add note')
            ->getControlPrototype()
            ->addClass('button');
        $form->onSuccess[] = $this->processAddForm;
        return $form;
    }
    
    public function processAddForm(\Nette\Application\UI\Form $form)
    {
        $jobUser = $this->jobService->getJobUser($this->jobUserId);
        if (!$jobUser) {
            $this->presenter->error();
        }
        $job = $this->jobService->find($jobUser->job_id);
        if (!$this->adminId && $job->company_id != $this->presenter->user->id) {
            $this->presenter->error();
        }
        
        $this->jobService->addNote($this->jobUserId, $form->values->note, $this->adminId);
        if ($this->presenter->isAjax()) {
            $this->invalidateControl('notes');
        } else {
            $this->redirect('this');
        }
    }
    
    public function handleEdit($id)
    {
        $this->editNoteId = $id;
        $this->invalidateControl('notes');
    }
    
    public function handleDelete($id)
    {
        $note = $this->jobService->getNote($id);
        if (!$note) {
            $this->presenter->error();
        }
        $jobUser = $this->jobService->getJobUser($note->job_user_id);
        $job = $this->jobService->find($jobUser->job_id);
        if (!$this->adminId && $job->company_id != $this->presenter->user->id) {
            $this->presenter->error();
        }
        $this->jobService->deleteNote($id);
        if ($this->presenter->isAjax()) {
            $this->invalidateControl('notes');
        } else {
            $this->redirect('this');
        }
    }
    
    public function createComponentEditForm()
    {
        return new \Nette\Application\UI\Multiplier($this->editFormFactory);
    }
        
    public function editFormFactory($id)
    {
        $form = new \Nette\Application\UI\Form;
        $form->getElementPrototype()
            ->addClass('ajax');
        $form->setTranslator($this->translator);
        
        $form->addTextArea('note')
            ->setRequired()
            ->getControlPrototype()
            ->addAttributes(['placeholder' => 'Write a note...'])
            ->addClass('edit-note-textarea');
        $save = $form->addSubmit('save', 'Save note');
        $save->onClick[] = $this->processEditFormSave;
        $save->getControlPrototype()
            ->addClass('button');
        $form->addSubmit('cancel', 'Cancel')
            ->getControlPrototype()
            ->addClass('button');
        $form->onSuccess[] = $this->processEditForm;
        foreach ($this->getNotes() as $row) {
            if ($row->id == $id) {
                $form['note']->setDefaultValue($row->note);
                break;
            }
        }
        return $form;
    }
    
    public function processEditFormSave(\Nette\Forms\Controls\SubmitButton $button)
    {
        $form = $button->form;
        $note = $this->jobService->getNote($form->name);
        if (!$note) {
            $this->presenter->error();
        }
        $jobUser = $this->jobService->getJobUser($note->job_user_id);
        $job = $this->jobService->find($jobUser->job_id);
        if (!$this->adminId && $job->company_id != $this->presenter->user->id) {
            $this->presenter->error();
        }
        $this->jobService->editNote($form->name, $form->values->note);
    }
    
    public function processEditForm(\Nette\Application\UI\Form $form)
    {
        if ($this->presenter->isAjax()) {
            $this->invalidateControl('notes');
        } else {
            $this->redirect('this');
        }
    }
    
}
