<?php

namespace App;

class CompanyProfileForm extends \AppForms\AppForms
{
    
    /** @var \Model\Service\CompanyService */
    protected $companyService;
    
    /** @var \Nette\Security\User */
    protected $user;
    
    /** @var \Model\Entity\Company\UserEntity */
    protected $companyEntity;
    
    public function __construct(\Nette\Application\UI\Presenter $presenter, \Model\Service\CompanyService $companyService, \Nette\Security\User $user)
    {
        parent::__construct(get_class($this), $presenter, FALSE);
        $this->ownTemplate = FALSE;
        $this->companyService = $companyService;
        $this->user = $user;
    }
    
    public function getCompanyEntity()
    {
        if (!isset($this->companyEntity)) {
            $this->companyEntity = $this->companyService->findUser($this->user->id);
        }
        return $this->companyEntity;
    }
    
    public function createComponent($name)
    {
        $company = $this->getCompanyEntity();
        $form = $this->form;
        $form->addText('company_name', 'Name')
            ->setDefaultValue($company->company_name);
        $form->addTextArea('description', 'Description')
            ->setDefaultValue($company->description);
        $form->addUpload('logo', 'Logo')
            ->addCondition(\Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::IMAGE);
//        $form->addUpload('picture', 'Picture');
        $form->addSubmit('save', 'Save');
        $form->onSuccess[] = $this->processForm;
        return $form;
    }
    
    public function processForm(\Nette\Application\UI\Form $form)
    {
        $values = $form->values;
        $company = $this->getCompanyEntity();
        $company->company_name = $values->company_name;
        $company->description = $values->description;
        $this->companyService->save($company, array('password', 'salt'));
        if ($values->logo->isImage()) {
            self::saveImg($values->logo, 'company', $company->id);
        }
        
        $this->presenter->flashMessage('saved');
        $this->presenter->redirect('this');
    }
    
}
