<?php

namespace Model\Mapper\Dibi;

class ChatDibiMapper extends DibiMapper
{

    /** @var string */
    protected $chat = 'chat';

    /** @var string */
    protected $chatMessage = 'chat_message';

    /** @var \Model\Service\CvService */
    protected $cvService;

    /** @var \Model\Service\JobService */
    protected $jobService;

    public function __construct(\DibiConnection $conn, \Model\Service\CvService $cvService, \Model\Service\JobService $jobService)
    {
        $this->cvService = $cvService;
        $this->jobService = $jobService;
        parent::__construct($conn);
    }

    /**
     * @return \Model\Entity\ChatEntity
     */
    public function loadChat($data)
    {
        $row = $this->conn
            ->select('text')
            ->from($this->chatMessage)
            ->where("{$this->chatMessage}.chat_id = %i", $data['id'])
            ->orderBy('id DESC')
            ->fetch();
        $entity = new \Model\Entity\ChatEntity;
		if ($row) {
        	$entity->lastMessage = $row->text;
		}
        foreach ($data as $key => $value) {
            $entity->{$key} = $value;
        }
        $entity->cv = $this->cvService->getDefaultCv($data->user_id);
        return $entity;
    }

    /**
     * @return \Model\Entity\ChatMessageEntity
     */
    public function loadChatMessage($data)
    {
        $entity = new \Model\Entity\ChatMessageEntity;
        foreach ($data as $key => $value) {
            $entity->{$key} = $value;
        }
        if ($entity->jobId) {
            $entity->job = $this->jobService->find($entity->jobId);
        }
        return $entity;
    }

    /**
     * @return \Model\Entity\ChatMessageEntity
     */
    public function getChatById($id)
    {
        $row = $this->getChatQuery()
            ->where("{$this->chat}.id = %i", $id)
            ->fetch();
        if (!$row) {
            return NULL;
        }
        return $this->loadChat($row);
    }

    public function getChat($userId, $companyId)
    {
        $row = $this->conn
            ->select('*')
            ->from($this->chat)
            ->where('user_id = %i AND company_id = %i', $userId, $companyId)
            ->fetch();
        if (!$row) {
            return NULL;
        }
        return $this->loadChat($row);
    }

    /**
     * @return \Model\Entity\ChatMessageEntity
     */
    public function getMessage($id)
    {
        $row = $this->conn
            ->select('*')
            ->from($this->chatMessage)
            ->where('id = %i', $id)
            ->fetch();
        return $this->loadChatMessage($row);
    }

    /**
     * @return \Model\Entity\ChatMessageEntity[]
     */
    public function findMessagesByChat($chatId)
    {
        $selection = $this->conn
            ->select('*')
            ->from($this->chatMessage)
            ->where('chat_id = %i', $chatId);
        $messages = array();
        foreach ($selection as $row) {
            $messages[] = $this->loadChatMessage($row);
        }
        return $messages;
    }

    public function chatToData(\Model\Entity\ChatEntity $entity)
    {
        return array(
            'user_id' => $entity->userId,
            'company_id' => $entity->companyId,
            'unreed' => $entity->unreed,
            'notifications_user' => $entity->getUserNotificationsType(),
            'notifications_company' => $entity->getCompanyNotificationsType(),
            'last_update' => $entity->getLastUpdate(),
        );
    }

    public function messageToData(\Model\Entity\ChatMessageEntity $entity)
    {
        return array(
            'chat_id' => $entity->chatId,
            'job_id' => $entity->jobId,
            'date' => $entity->date,
            'text' => $entity->text,
            'sender' => $entity->sender,
        );
    }

    public function saveChat(\Model\Entity\ChatEntity $entity)
    {
        $data = $this->chatToData($entity);
        if ($entity->id) {
            $this->conn->update($this->chat, $data)
                ->where('id = %i', $entity->id)
                ->execute();
        } else {
            $entity->id = $this->conn
                ->insert($this->chat, $data)
                ->execute(\dibi::IDENTIFIER);
        }
    }

    public function saveMessage(\Model\Entity\ChatMessageEntity $entity)
    {
        $data = $this->messageToData($entity);
        if ($entity->id) {
            $this->conn->update($this->chatMessage, $data)
                ->where('id = %i', $entity->id)
                ->execute();
        } else {
            $entity->id = $this->conn
                ->insert($this->chatMessage, $data)
                ->execute(\dibi::IDENTIFIER);
        }
    }

    public function chatExist($userId, $companyId)
    {
        return (bool) $this->conn
            ->select('id')
            ->from($this->chat)
            ->where('user_id = %i AND company_id = %i', $userId, $companyId)
            ->fetch();
    }

    public function findChatsByUser($userId, $limit = NULL)
    {
        $selection = $this->getChatQuery()
            ->where('chat.user_id = %i', $userId)
            ->orderBy('last_update DESC');
        if ($limit) {
            $selection = $selection->limit($limit);
        }
        $entities = array();
        foreach ($selection as $row) {
            $entities[] = $this->loadChat($row);
        }
        return $entities;
    }

    public function findChatsByCompany($companyId)
    {
        $selection = $this->getChatQuery()
            ->where('company_id = %i', $companyId)
            ->orderBy('last_update DESC');
        $entities = array();
        foreach ($selection as $row) {
            $entities[] = $this->loadChat($row);
        }
        return $entities;
    }

    public function findUnreadCountByUser($userId)
    {
        $selection = $this->getChatQuery()
            ->where('chat.user_id = %i', $userId)
            ->where('chat.unreed = %i', \Model\Entity\ChatEntity::UNREED_USER);
        return $selection->count();
    }

    public function findUnreadCountByCompany($companyId)
    {
        $selection = $this->getChatQuery()
            ->where('company_id = %i', $companyId)
            ->where('chat.unreed = %i', \Model\Entity\ChatEntity::UNREED_COMPANY);
        $unread = 0;
        foreach ($selection as $row) {
            $entity = $this->loadChat($row);
            if ($entity->getIs_completed()) {
                $unread++;
            }
        }
        return $unread;
    }

    /**
     * @return \DibiFluent
     */
    private function getChatQuery()
    {
        return $this->conn
            ->select("{$this->chat}.*, candidate_users.company_name AS companyName")
            ->from($this->chat)
            ->leftJoin('candidate_users')->on('candidate_users.id = company_id');
    }

}
