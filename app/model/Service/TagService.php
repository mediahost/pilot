<?php

namespace Model\Service;

class TagService
{
    
    /** @var \Model\Mapper\Dibi\TagDibiMapper */
    protected $mapper;
    
    function __construct(\Model\Mapper\Dibi\TagDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function findAll()
    {
        return $this->mapper->findAll();
    }
    
    public function saveTag($tag)
    {
        return $this->mapper->saveTag($tag);
    }
    
    public function saveUserTags($user, $tags)
    {
        $this->mapper->saveUserTags($user, $tags);
    }
    
    public function getUserTags($user)
    {
        return $this->mapper->getUserTags($user);
    }
    
    public function getUserTagNames($user)
    {
        return $this->mapper->getUserTagNames($user);
    }
    
}
