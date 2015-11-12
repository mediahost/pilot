<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Entity\JobEntity,
    Model\Entity\UserEntity,
    Model\Service\JobApplyService,
    Model\Service\MailService,
    Model\Service\CvService,
    Model\Entity\ActionLogEntity;

/**
 * Job Apply Form
 *
 * @author Petr Poupě
 */
class JobApplyForm extends AppForms
{

    /** @var JobApplyService */
    private $jobapplys;

    /** @var MailService */
    private $mail;

    /** @var CvService */
    private $cvService;

    /** @var array */
    private $cvs;

    /** @var JobEntity */
    protected $jobEntity;

    /** @var UserEntity */
    protected $userEntity;

    public function __construct(Presenter $presenter, JobApplyService $jobapplys, MailService $mail, CvService $cvService, $cvs)
    {
        parent::__construct(get_class($this), $presenter);

        $this->jobapplys = $jobapplys;
        $this->mail = $mail;
        $this->cvService = $cvService;
        $this->cvs = $cvs;
    }

    public function setDefaults(JobEntity $job, UserEntity $user)
    {
        $this->jobEntity = $job;
        $this->userEntity = $user;

        $this->entityToForm($job, $user);
    }

    protected function createComponent($name)
    {
        $this->form->getElementPrototype()->class = "styled innerPage";

        $this->form->addText('name', 'Your name')
                ->addRule(Form::FILLED, "Please enter your name!");
        $this->form->addText('sender', 'Your email')
                ->setEmptyValue("@")
                ->addRule(Form::EMAIL, "Entered value is not email!");
        $this->form->addText('subject', 'Ref. number')->setAttribute("readonly", "readonly");
        $this->form->addTextArea('message', 'Message')
                ->addRule(Form::FILLED, "Please don't send empty messages!");
        $this->form->addMultiSelect('cvs', "Append CV", $this->cvs);
        $this->form->addUpload('file', "Attachement");

        $this->form->addSubmit('send', 'Send');

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $subject = "pilotincommand.com " . $form->values->subject;
        $tmpSaveDir = \CommonHelpers::concatStrings("/", ".", "tmp", $this->userEntity->id, "attachments");

        $attachments = array();
        if (\CommonHelpers::dir_exists($tmpSaveDir) && $form->values->file->isOk()) {
            $filepath = \CommonHelpers::concatStrings("/", $tmpSaveDir, $form->values->file->name);
            $form->values->file->move($filepath);
            $attachments[] = realpath($filepath);
        }

        $cvPrinter = new \CvPrinter($this->presenter->context->users);
        foreach ($form->values->cvs as $cvId) {
            $cv = $this->cvService->getCv($cvId);
            $cvNamePrefix = $this->user->getIdentity()->first_name;
            $cvNamePrefix .= " " . $this->user->getIdentity()->last_name;
            $cvNamePrefix .= " " . $cv->name;
            if ($cv) {
                $pdf = $cvPrinter->generate($cv, $this->presenter, $this->translator, $this->presenter->pageInfo);
                $dirName = \CommonHelpers::concatStrings("/", ".", "tmp", $cv->userId, "cv_saved") . "/";
                if (\CommonHelpers::dir_exists($dirName)) {
                    $cvName = \Nette\Utils\Strings::webalize($cvNamePrefix . " " . $cv->id);
                    $attachments[] = realpath($pdf->save($dirName, $cvName));
                }
            }
        }
        $mail = $this->mail->create($this->lang);
        $mail->selectMail(\Model\Service\MailFactory::PRIVATE_MAIL_JOB_APPLY, array(
            'to' => $this->jobEntity->ref_email,
            'from' => $form->values->sender,
            'name' => $form->values->name,
            'subject' => "Refnr: " . $this->jobEntity->ref_num,
            'message' => $form->values->message,
            'attachmets' => $attachments,
        ));
        $mail->send();

        list($name) = explode(' ', $form->values->name);
        $mailToUser = $this->mail->create($this->lang);
        $mailToUser->selectMail(\Model\Service\MailFactory::MAIL_THANKS_FOR_APPLY, array(
            'to' => $form->values->sender,
            'name' => $name,
            'refnr' => $this->jobEntity->ref_num,
        ));
        $mailToUser->send();


        if ($this->user->getId() !== NULL) {
            $apply = $this->jobapplys->apply($this->userEntity->id, $this->jobEntity->id, $this->jobEntity->name, $this->jobEntity->ref_email, $form->values->sender, $subject, $form->values->message);

            $this->presenter->context->actionlogs->log(ActionLogEntity::JOB_APPLY, $this->presenter->user->getId(), array($apply->id));
        }
        $this->presenter->flashMessage('Your request has been sent to company. Please wait for their response.' . PHP_EOL . PHP_EOL . 'Good luck!', 'success');
        $this->presenter->redirect("Dashboard:jobs");
    }

    private function entityToForm(JobEntity $job, UserEntity $user)
    {
        parent::setDefaultValues(array(
            "name" => $user->fullName,
            "sender" => $user->mail,
            "subject" => "Refnr: " . $job->ref_num,
            "message" => $this->translator->translate("I would like to apply for this position. Please find enclosed my CV. Best regards"),
        ));
    }

}

?>
