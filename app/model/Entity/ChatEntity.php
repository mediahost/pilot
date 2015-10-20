<?php

namespace Model\Entity;

/**
 * @property int $id
 * @property int $userId
 * @property string $userName
 * @property int $companyId
 * @property string $companyName
 * @property int $unreed
 * @property \Nette\DateTime $lastUpdate
 */
class ChatEntity extends Entity
{
    
    const NOTIFICATION_DISABLED = 0,
        NOTIFICATION_ENABLED = 1,
        NOTIFICATION_GLOBAL = 2;
    
    const UNREED_FALSE = 0,
        UNREED_USER = 1,
        UNREED_COMPANY = 2;
    
    /** @var int */
    protected $id;
    
    /** @var int */
    protected $user_id;
    
    /** @var int */
    protected $company_id;
    
    /** @var string */
    protected $companyName;
    
    /** @var int */
    protected $unreed = self::UNREED_FALSE;
    
    /** @var int */
    protected $notifications_user = self::NOTIFICATION_GLOBAL;
    
    /** @var int */
    protected $notifications_company = self::NOTIFICATION_GLOBAL;
    
    /** @var DateTime */
    protected $last_update;
    
    /** @var string */
    protected $lastMessage;
    
    /** @var CvEntity */
    protected $cv;
    
    public function __construct()
    {
        $this->last_update = new \Nette\DateTime;
    }
    
    public function getUserId()
    {
        return $this->user_id;
    }
    
    public function getIs_completed()
    {
        return $this->cv->isCompleted();
    }
    
    public function getUserName()
    {
        return $this->cv->getFullName();
    }
    
    public function getCv_photo()
    {
        return $this->cv->photo;
    }

    public function getCompanyId()
    {
        return $this->company_id;
    }
    
    public function getLastUpdate()
    {
        return $this->last_update;
    }
    
    public function isLastUpdateToday()
    {
        $date = new \DateTime($this->last_update);
        $diff = $date->diff(new \DateTime);
        if ($diff->y | $diff->m | $diff->d) {
            return FALSE;
        }
        return TRUE;
    }
    
    public function getLastMessage()
    {
        return $this->lastMessage;
    }
    
    public function setLastUpdate(\Nette\DateTime $date = NULL)
    {
        if (!$date) {
            $date = new \Nette\DateTime;
        }
        $this->last_update = $date;
    }

    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    public function setCompanyId($companyId)
    {
        $this->company_id = $companyId;
    }

    public function getUserNotificationsType()
    {
        return $this->notifications_user;
    }
    
    public function setUserNotificationType($type)
    {
        $this->notifications_user = $type;
    }
    
    public function setCompanyNotificationType($type)
    {
        $this->notifications_company = $type;
    }

    public function getCompanyNotificationsType()
    {
        return $this->notifications_company;
    }
    
    public function isUnreedByUser()
    {
        return $this->unreed == self::UNREED_USER;
    }
    
    public function isUnreedByCompany()
    {
        return $this->unreed == self::UNREED_COMPANY;
    }
    
}
