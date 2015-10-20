<?php

namespace AppForms;

class AddCompanyPicsForm extends AppForms
{

    /** @var \Model\Service\CompanyService */
    protected $companyService;
    
    /** @var \Nette\Security\User */
    protected $user;

    function __construct($presenter, \Model\Service\CompanyService $companyService, \Nette\Security\User $user)
    {
        parent::__construct(get_class($this), $presenter, FALSE);
        $this->companyService = $companyService;
        $this->user = $user;
    }
    
    public function handleDeletePic($id)
    {
        $this->presenter->redirect("deleteCompanyPicture!", $id);
    }

    public function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "dropzone";
        $this->form->getElementPrototype()->id = "picsDropzone";
        $this->form->onSuccess[] = $this->processForm;
        $this->template->presenter = $this->presenter;
        return $this->form;
    }

    public function processForm(\Nette\Application\UI\Form $form)
    {
        if (!empty($_FILES)) {
            $file = new \Nette\Http\FileUpload($_FILES['file']);
            if ($file->isImage()) {
                $company = $this->companyService->findUser($this->user->id);
                $this->companyService->addCompanyPicture($file, $company);
            }
        }
        $this->invalidateControl("pics");
    }
    
    public function render()
    {
        $this->template->company = $this->companyService->findUser($this->user->id);
        parent::render();
    }

}
