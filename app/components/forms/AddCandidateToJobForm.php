<?php

namespace AppForms;

class AddCandidateToJobForm extends AppForms
{
    
    /** @var \Model\Service\JobService */
    protected $jobService;
    
    /** @var \Model\Service\CvService */
    protected $cvService;
    
    /** @var \Model\Service\MailService */
    protected $mailService;
    
    public function __construct(\Nette\Application\UI\Presenter $presenter, 
        \Model\Service\JobService $jobService, 
        \Model\Service\CvService $cvService,
        \Model\Service\MailService $mailService)
    {
        $this->jobService = $jobService;
        $this->cvService = $cvService;
        $this->mailService = $mailService;
        parent::__construct(get_class($this), $presenter, TRUE);
    }
    
    public function createComponent($name)
    {
        $this->setStyle(AppForms::STYLE_METRONIC);
        
        $form = $this->form;
        $form->getElementPrototype()
            ->addClass('form-action-no-bg');
        $form->addSelect('user', 'Candidates', $this->cvService->getAllCandidateNames($this->presenter->getParameter('id')))
            ->setRequired()
            ->setAttribute('class', 'chosen');
        $form->addSubmit('add', 'Add');
        $form->onSuccess[] = $this->processForm;
        return $form;
    }
    
    public function processForm($form)
    {
        $job = $this->jobService->find($this->presenter->getParameter('id'));
        $cv = $this->cvService->getDefaultCv($form->values->user);
        $this->jobService->addUserToJob($job->id, $form->values->user);
        $mail = $this->mailService->create($this->lang);
        $mail->selectMail(\Model\Service\MailFactory::MAIL_MATCHED_NOTIFY, array(
            'to' => $cv->email,
            'job_link' => $this->presenter->link('//:Front:Jobs:show', $job->code),
            'job_name' => $job->name,
            'company_name' => $job->company,
            'candidate_name' => $cv->getFullName(),
        ));
        $mail->send();
        
        $this->presenter->redirect('this');
    }
    
}
