<?php

namespace Model\Service;

use Model\Mapper\Dibi\ChatDibiMapper,
    Model\Entity\ChatEntity;

class ChatService extends \Nette\Object
{
    
    /** @var ChatDibiMapper */
    protected $chatDibiMapper;
    
    public function __construct(ChatDibiMapper $chatDibiMapper)
    {
        $this->chatDibiMapper = $chatDibiMapper;
    }
    
    private function createChat($userId, $companyId)
    {
        $chat = new ChatEntity();
        $chat->userId = $userId;
        $chat->companyId = $companyId;
        $this->chatDibiMapper->saveChat($chat);
        return $chat;
    }
    
    public function chatExist($userId, $companyId)
    {
        return $this->chatDibiMapper->chatExist($userId, $companyId);
    }
    
    /**
     * @return \Model\Entity\ChatEntity
     */
    public function getChat($userId, $companyId)
    {
        $chat = $this->chatDibiMapper->getChat($userId, $companyId);
        if (!$chat) {
            $chat = $this->createChat($userId, $companyId);
        }
        return $chat;
    }
    
    public function findChatsByCompany($companyId)
    {
        return $this->chatDibiMapper->findChatsByCompany($companyId);
    }
    
    public function findChatsByUser($userId, $limit = NULL)
    {
        return $this->chatDibiMapper->findChatsByUser($userId, $limit);
    }
    
    public function getChatById($id)
    {
        return $this->chatDibiMapper->getChatById($id);
    }
    
    public function saveMessage(\Model\Entity\ChatMessageEntity $message, ChatEntity $chat)
    {
        $message->chatId = $chat->id;
        $chat->unreed = $message->isUserSender() ? ChatEntity::UNREED_COMPANY : ChatEntity::UNREED_USER;
        $chat->setLastUpdate();
        
        $this->saveChat($chat);
        $this->chatDibiMapper->saveMessage($message);
    }
    
    public function saveChat(ChatEntity $chat)
    {
        $this->chatDibiMapper->saveChat($chat);
    }
    
    public function findMessages($chatId)
    {
        return $this->chatDibiMapper->findMessagesByChat($chatId);
    }
    
    public function readChatByUser(ChatEntity $chat)
    {
        if ($chat->isUnreedByUser()) {
            $chat->unreed = ChatEntity::UNREED_FALSE;
            $this->saveChat($chat);
        }
    }
    
    public function readChatByCompany(ChatEntity $chat)
    {
        if ($chat->isUnreedByCompany()) {
            $chat->unreed = ChatEntity::UNREED_FALSE;
            $this->saveChat($chat);
        }
    }
    
    public function getUnreadCountByUser($userId)
    {
        return $this->chatDibiMapper->findUnreadCountByUser($userId);
    }
    
    public function getUnreadCountByCompany($companyId)
    {
        return $this->chatDibiMapper->findUnreadCountByCompany($companyId);
    }
    
}
