<?php

namespace App\Components;

class JobUserNotesFactory extends \Nette\Object
{
    
    /** @var \Model\Service\JobService */
    protected $jobService;
    
    /** @var \Nette\Localization\ITranslator */
    protected $translator;
    
    /** @var \Model\Service\CompanyService */
    protected $companyService;
    
    /** @var \Model\Service\UserService */
    protected $userService;
    
    public function __construct(\Model\Service\JobService $jobService, 
        \Nette\Localization\ITranslator $translator,
        \Model\Service\CompanyService $companyService,
        \Nette\DI\Container $dic)
    {
        $this->jobService = $jobService;
        $this->translator = $translator;
        $this->companyService = $companyService;
        $this->userService = $dic->users;
    }
    
    public function create($id)
    {
        return new JobUserNotes($id, $this->jobService, $this->translator, $this->companyService, $this->userService);
    }
    
}
