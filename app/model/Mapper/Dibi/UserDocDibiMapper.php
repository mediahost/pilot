<?php

namespace Model\Mapper\Dibi;

class UserDocDibiMapper extends DibiMapper
{
    
    /** @var type */
    protected $userDocTableName = 'user_doc';
    
    public function save(\Model\Entity\UserDocEntity $userDoc)
    {
        $data = $this->itemToData($userDoc);
        if ($userDoc->id === NULL) {
            $userDoc->id = $this->conn
                ->insert($this->userDocTableName, $data)
                ->execute(\dibi::IDENTIFIER);
        } else {
            $this->conn->update($this->userDocTableName, $data)
                ->where('id = %i', $userDoc->id)
                ->execute();
        }
    }
    
    public function itemToData(\Model\Entity\UserDocEntity $userDoc)
    {
        return array(
            'user_id' => $userDoc->userId,
            'created' => $userDoc->created,
            'name' => $userDoc->name,
            'original_name' => $userDoc->originalName,
            'public' => (int) $userDoc->public,
        );
    }
    
    public function load($data)
    {
        $userDoc = new \Model\Entity\UserDocEntity;
        $userDoc->id = $data['id'];
        $userDoc->userId = $data['user_id'];
        $userDoc->created = \Nette\DateTime::from($data['created']);
        $userDoc->name = $data['name'];
        $userDoc->originalName = $data['original_name'];
        $userDoc->public = (bool) $data['public'];
        return $userDoc;
    }
    
    public function findByUser($userId, $limit = NULL, $public = NULL)
    {
        $selection = $this->conn
            ->select('*')
            ->orderBy('created DESC')
            ->from($this->userDocTableName)
            ->where('user_id = %i', $userId);
        if ($public === TRUE) {
            $selection->where('public = %i', 1);
        } elseif ($public === FALSE) {
            $selection->where('public = %i', 0);
        }
        if ($limit) {
            $selection->limit($limit);
        }
        return $this->loadMultiple($selection);
    }
    
    public function loadMultiple(\DibiFluent $selection)
    {
        $return = array();
        foreach ($selection->execute() as $row) {
            $return[] = $this->load($row);
        }
        return $return;
    }
    
    public function delete(\Model\Entity\UserDocEntity $userDoc)
    {
        return $this->conn->delete($this->userDocTableName)->where("id = %i", $userDoc->id)->execute();
    }
    
    /**
     * @return \Model\Entity\UserDocEntity
     */
    public function get($id)
    {
        $row = $this->conn
            ->select('*')
            ->from($this->userDocTableName)
            ->where('id = %i', $id)
            ->fetch();
        return $this->load($row);
    }
    
}
