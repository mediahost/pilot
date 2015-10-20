<?php

namespace AppForms;

class AddUserDocForm extends AppForms
{

    /** @var \Model\Service\UserDocService */
    protected $userDocService;

    function __construct($presenter, \Model\Service\UserDocService $userDocService)
    {
        parent::__construct(get_class($this), $presenter, FALSE);
        $this->userDocService = $userDocService;
    }
    
    public function handleDeleteDoc($id)
    {
        $this->presenter->redirect("deleteDoc!", $id);
    }
    
    public function handleSwitchDocVisibility($id)
    {
        $this->presenter->redirect("switchDocVisibility!", $id);
    }

    public function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "dropzone";
        $this->form->getElementPrototype()->id = "docsDropzone";
        $this->form->onSuccess[] = $this->processForm;
        $this->template->presenter = $this->presenter;
        return $this->form;
    }

    public function processForm(\Nette\Application\UI\Form $form)
    {
        if (!empty($_FILES)) {
            $file = new \Nette\Http\FileUpload($_FILES['file']);
            $this->userDocService->create($file, $this->user->id);
        }
        $this->invalidateControl("files");
    }
    
    public function render()
    {
        $this->template->docs = $this->userDocService->findByUser($this->user->id);
        parent::render();
    }

}
