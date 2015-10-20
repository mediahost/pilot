<?php

namespace Model\Mapper\Dibi;

class TagDibiMapper extends DibiMapper
{
    
    public function findAll()
    {
        return $this->conn->select(array('tag.id' => 'id', 'name' => 'name'))
            ->from('tag')
            ->join('user_tag')->on('user_tag.tag_id = tag.id')
            ->groupBy('tag.id')
            ->fetchPairs('id', 'name');
    }
    
    public function saveTag($tag)
    {
        $tagRow = $this->conn->select('id')
            ->from('tag')
            ->where('name = %s', $tag)
            ->fetch();
        if ($tagRow) {
            return $tagRow->id;
        }
        return $this->conn->insert('tag', ['name' => $tag])->execute(\dibi::IDENTIFIER);
    }
    
    public function saveUserTags($user, $tags)
    {
        $this->conn->delete('user_tag')
            ->where('user_id = %i', $user)
            ->execute();
        if (count($tags) == 0) {
            return;
        }
        $tags = array_unique($tags);
        $data = [];
        foreach ($tags as $tag) {
            $data[] = [
                'user_id' => $user,
                'tag_id' => $tag,
            ];
        }
        $this->conn->query("INSERT INTO [user_tag] %ex", $data);
    }
    
    public function getUserTags($user)
    {
        return $this->conn->select('id, tag_id')
            ->from('user_tag')
            ->where('user_id = %i', $user)
            ->fetchPairs();
    }
    
    public function getUserTagNames($user)
    {
        return $this->conn->select('tag.id, tag.name')
            ->from('user_tag')
            ->join('tag')->on('tag.id = user_tag.tag_id')
            ->where('user_id = %i', $user)
            ->fetchPairs();
    }
    
}
