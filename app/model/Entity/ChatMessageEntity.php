<?php

namespace Model\Entity;

/**
 * @property int $id
 * @property int $chatId
 * @property int $jobId
 * @property \Nette\DateTime $date
 * @property string $text
 * @property int $sender
 */
class ChatMessageEntity extends Entity
{
    
    const SENDER_USER = 1,
        SENDER_COMPANY = 2;
    
    /** @var int */
    protected $id;
    
    /** @var int */
    protected $chat_id;
    
    /** @var int */
    protected $job_id;
    
    /** @var \Nette\DateTime */
    protected $date;
    
    /** @var string */
    protected $text;
    
    /** @var int */
    protected $sender;
    
    /** @var JobEntity */
    protected $job;
    
    public function __construct()
    {
        $this->date = new \Nette\DateTime;
    }
    
    public function isUserSender()
    {
        return $this->sender == self::SENDER_USER;
    }
    
    public function isCompanySender()
    {
        return $this->sender == self::SENDER_COMPANY;
    }
    
    public function setSenderAsUser()
    {
        $this->sender = self::SENDER_USER;
    }
    
    public function setSenderAsCompany()
    {
        $this->sender = self::SENDER_COMPANY;
    }
    
    public function setChatId($id)
    {
        $this->chat_id = $id;
    }
    
    public function getChatId()
    {
        return $this->chat_id;
    }
    
    public function getJobId()
    {
        return $this->job_id;
    }

    public function setJobId($jobId)
    {
        $this->job_id = $jobId;
    }
    
}
