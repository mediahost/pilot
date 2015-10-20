<?php

namespace AppForms;

use Nette\Application\UI\Presenter,
    Model\Service\ChatService,
    Model\Service\MailService;

class ChatMessageForm extends AppForms
{
    
    const SENDER_USER = 'user',
        SENDER_COMPANY = 'company';


    /** @var int */
    protected $userId;
    
    /** @var int */
    protected $companyId;
    
    /** @var \Model\Entity\ChatEntity */
    protected $chat;
    
    protected $sender;
    
    /** @var ChatService */
    protected $chatService;
    
    /** @var \Model\Service\MailService */
    protected $mailService;
    
    /** @var string */
    protected $redirect = 'this';
    
    /** @var array */
    protected $redirectArgs = array();
    
    /** @var string */
    protected $messagesDestination = NULL;
    
    /** @var array */
    protected $messagesArgs = array();
    
    /** @var bool */
    protected $showNotifySettings;
    
    /** @var string */
    protected $globalSettingDestination = NULL;
    
    /** @var bool */
    protected $textRequired = FALSE;
    
    /** @var \Model\Entity\JobEntity */
    protected $job;
    
    /** @var \Nette\Callback */
    public $onSend = NULL;
    
    public function __construct(Presenter $presenter, ChatService $chatService, MailService $mailService, $showNotifySettings = FALSE, $textRequired = FALSE)
    {
        $this->chatService = $chatService;
        $this->mailService = $mailService;
        $this->showNotifySettings = $showNotifySettings;
        $this->textRequired = $textRequired;
        parent::__construct('chatmessageform', $presenter, FALSE);
    }
    
    public function setJob(\Model\Entity\JobEntity $job)
    {
        $this->job = $job;
    }
    
    public function render()
    {
        $this->template->showNotifySettings = $this->showNotifySettings;
        $this->template->globalSettingDestination = $this->globalSettingDestination;
        parent::render();
    }
    
    public function setGlobalSettingDestination($destination)
    {
        $this->globalSettingDestination = $destination;
    }
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }
    
    public function setSender($sender)
    {
        $this->sender = $sender;
    }
    
    public function setRedirect($destination, $args = array())
    {
        $this->redirect = $destination;
        $this->redirectArgs = $args;
    }
    
    public function setMessagesLink($destination, $args = array())
    {
        $this->messagesDestination = $destination;
        $this->messagesArgs = $args;
    }
    
    public function createComponent($name)
    {
        
        $this->form->addTextArea('text', 'Message')
            ->setAttribute("placeholder", $this->translator->translate("Write a message..."));
        if ($this->textRequired) {
            $this->form['text']->setRequired();
        }
        $this->form->addRadioList('notification', NULL, array(
            \Model\Entity\ChatEntity::NOTIFICATION_DISABLED => 'disabled',
            \Model\Entity\ChatEntity::NOTIFICATION_ENABLED => 'enabled',
            \Model\Entity\ChatEntity::NOTIFICATION_GLOBAL => 'by global setting',
        ));
        $this->form->addSubmit('send', 'Send message');
        
        $this->form->onSuccess[] = $this->processForm;
            
        return $this->form;
    }
    
    protected function attached($presenter)
    {
        $form = $this->getForm();
        if ($this->chatService->chatExist($this->userId, $this->companyId)) {
            $this->chat = $this->chatService->getChat($this->userId, $this->companyId);
            if ($this->sender == self::SENDER_USER) {
                $form['notification']->setDefaultValue($this->chat->getUserNotificationsType());
            } else {
                $form['notification']->setDefaultValue($this->chat->getCompanyNotificationsType());
            }
        } else {
            $form['notification']->setDefaultValue(\Model\Entity\ChatEntity::NOTIFICATION_GLOBAL);
        }
        parent::attached($presenter);
    }
    
    public function processForm(\Nette\Application\UI\Form $form)
    {
        $values = $form->values;
        if ($this->chat) {
            $chat = $this->chat;
        } else {   
            $chat = $this->chatService->getChat($this->userId, $this->companyId);
        }
        
        if ($this->showNotifySettings) {
            if ($this->sender == self::SENDER_USER) {
                $chat->setUserNotificationType($values->notification);
            } else {
                $chat->setCompanyNotificationType($values->notification);
            }
        }
        
        $text = \Nette\Utils\Strings::trim($values->text);
        if (empty($text)) {
            $this->chatService->saveChat($chat);
            if ($this->presenter->isAjax()) {
                $this->presenter->sendPayload();
            }
            $this->presenter->redirect($this->redirect, $this->redirectArgs);
        }
        
        $message = new \Model\Entity\ChatMessageEntity;
        if (isset($this->job)) {
            $message->jobId = $this->job->id;
        }
        
        $companyService = $this->presenter->context->getByType('Model\Service\CompanyService');
        $company = $companyService->findUser($this->companyId);
        
        $users = $this->presenter->context->users;
        $user = $users->find($this->userId);
        
        if ($this->sender == self::SENDER_USER) {
            $message->setSenderAsUser();
            
            $from = $user->firstName . ' ' . $user->lastName;
            $to = $company->email;
            
            if ($chat->getCompanyNotificationsType() == \Model\Entity\ChatEntity::NOTIFICATION_GLOBAL) {
                $notify = $company->chat_notifications;
            } else {
                $notify = $chat->getCompanyNotificationsType() == \Model\Entity\ChatEntity::NOTIFICATION_ENABLED;
            }
        } else {
            $from = $company->company_name;
            $to = $user->mail;
            $message->setSenderAsCompany();
            
            if ($chat->getUserNotificationsType() == \Model\Entity\ChatEntity::NOTIFICATION_GLOBAL) {
                $notify = $user->chat_notifications;
            } else {
                $notify = $chat->getUserNotificationsType() == \Model\Entity\ChatEntity::NOTIFICATION_ENABLED;
            }
        }
        $message->text = $text;
        $this->chatService->saveMessage($message, $chat);
        
        
        if ($notify) {
            $a = FALSE;
            if (isset($this->job)) {
                $a = \Nette\Utils\Html::el('a');
                $a->href($this->presenter->link('//:Front:Jobs:show', $this->job->code));
                $a->setText($this->job->name);
            }
            $mail = $this->mailService->create('');
            $mail->selectMail(\Model\Service\MailFactory::MAIL_CHAT_NOTIFY, array(
                'message_from' => $from,
                'message_text' => $text,
                'to' => $to,
                'job_link' => $a,
            ));
            $mail->send();
        }
        if ($this->messagesDestination) {
            $messagesLink = $this->presenter->link($this->messagesDestination, $this->messagesArgs);
            $this->presenter->flashMessage(\Nette\Utils\Html::el()->setHtml('Your message has been sent. Go to <a href="'.$messagesLink.'">messages</a>.'), 'success');
        }
        if ($this->onSend) {
            $cb = new \Nette\Callback($this->onSend);
            $cb->invoke();
        }
        $this->presenter->redirect($this->redirect, $this->redirectArgs);
    }
    
    public function getElementPrototype()
    {
        return $this->getForm()->getElementPrototype();
    }
    
}
